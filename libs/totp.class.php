<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
 *   sendtogeo@gmail.com   												   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

/**
 * TOTP (RFC 6238, built on the HOTP algorithm in RFC 4226), pure PHP, no
 * external dependency - compatible with Google Authenticator, Authy, and
 * any other standard authenticator app. Verified against RFC 6238
 * Appendix B's official test vectors - see spTests/tests/php/
 * totp_test.php.
 */
class Totp {

	const TIME_STEP = 30;
	const DIGITS = 6;

	// a fresh, random 160-bit (20-byte) secret - the conventional TOTP
	// secret length used by every major authenticator app
	public static function generateSecret() {
		return random_bytes(20);
	}

	// RFC 4648 base32 (no padding needed for a fixed-length secret, but
	// produced anyway for strict RFC compliance / apps that expect it) -
	// this is the form users type/scan into an authenticator app, since
	// raw binary can't be displayed or QR-encoded safely
	public static function base32Encode($data) {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$bits = '';
		for ($i = 0; $i < strlen($data); $i++) {
			$bits .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
		}
		$output = '';
		foreach (str_split($bits, 5) as $chunk) {
			if (strlen($chunk) < 5) {
				$chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
			}
			$output .= $alphabet[bindec($chunk)];
		}
		while (strlen($output) % 8 !== 0) {
			$output .= '=';
		}
		return $output;
	}

	public static function base32Decode($base32) {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$base32 = strtoupper(rtrim($base32, '='));
		$bits = '';
		for ($i = 0; $i < strlen($base32); $i++) {
			$pos = strpos($alphabet, $base32[$i]);
			if ($pos === false) {
				continue; // skip any stray non-alphabet character (e.g. a space a user pasted)
			}
			$bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
		}
		$output = '';
		foreach (str_split($bits, 8) as $byte) {
			if (strlen($byte) === 8) {
				$output .= chr(bindec($byte));
			}
		}
		return $output;
	}

	// the HOTP algorithm (RFC 4226) that TOTP is built on - $counter is
	// the number of TIME_STEP-second windows since the Unix epoch for
	// TOTP's use, but kept as a separate, directly-testable step since
	// that's how RFC 6238's own test vectors are structured
	public static function hotp($secretBytes, $counter, $digits = self::DIGITS) {
		$counterBytes = pack('N*', 0) . pack('N*', $counter); // 8-byte big-endian counter
		$hash = hash_hmac('sha1', $counterBytes, $secretBytes, true);
		$offset = ord($hash[strlen($hash) - 1]) & 0xf;
		$binary =
			((ord($hash[$offset]) & 0x7f) << 24) |
			((ord($hash[$offset + 1]) & 0xff) << 16) |
			((ord($hash[$offset + 2]) & 0xff) << 8) |
			(ord($hash[$offset + 3]) & 0xff);
		$code = $binary % (10 ** $digits);
		return str_pad((string) $code, $digits, '0', STR_PAD_LEFT);
	}

	public static function getCode($secretBytes, $timestamp = null, $timeStep = self::TIME_STEP, $digits = self::DIGITS) {
		$timestamp = $timestamp === null ? time() : $timestamp;
		$counter = (int) floor($timestamp / $timeStep);
		return self::hotp($secretBytes, $counter, $digits);
	}

	// checks the current time window AND $window steps on either side
	// (default: 1 step = ±30s, a 90-second total tolerance window) to
	// absorb realistic clock drift between the server and the user's
	// phone - a code is still only ever valid within that bounded range,
	// not indefinitely. hash_equals() for a timing-safe comparison, same
	// discipline already used for this app's other secret comparisons
	// (e.g. OAuth state, ping trigger secret).
	public static function verifyCode($secretBytes, $code, $window = 1, $timeStep = self::TIME_STEP, $digits = self::DIGITS) {
		$code = preg_replace('/\s+/', '', (string) $code);
		if (!preg_match('/^\d{' . $digits . '}$/', $code)) {
			return false;
		}
		$currentCounter = (int) floor(time() / $timeStep);
		for ($i = -$window; $i <= $window; $i++) {
			$candidate = self::hotp($secretBytes, $currentCounter + $i, $digits);
			if (hash_equals($candidate, $code)) {
				return true;
			}
		}
		return false;
	}

	// the standard otpauth:// URI every authenticator app recognizes -
	// pasting/scanning this both fills in the secret AND labels the
	// entry with this app's name + the account it belongs to, instead of
	// the generic "Unknown" an app shows for a bare manually-typed secret
	public static function buildOtpAuthUri($secretBytes, $accountName, $issuer = 'SEO Panel') {
		$label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
		$secret32 = self::base32Encode($secretBytes);
		return 'otpauth://totp/' . $label
			. '?secret=' . $secret32
			. '&issuer=' . rawurlencode($issuer)
			. '&algorithm=SHA1'
			. '&digits=' . self::DIGITS
			. '&period=' . self::TIME_STEP;
	}
}
