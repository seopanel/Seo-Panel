<?php
/**************************************************************************
*   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	          *
*   sendtogeo@gmail.com   											      *
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
 * Class defines all user token controller functions
 */
class UserTokenController extends Controller {

	// which user_tokens columns hold OAuth secrets (long-lived Google
	// Analytics/Search Console bearer credentials) - encrypted at rest
	// below, since a DB compromise (backup leak, insider, a future SQLi)
	// would otherwise yield live, usable third-party credentials for
	// every connected user, not just this app's own sessions
	var $encryptedFields = array('access_token', 'refresh_token');

	/*
	 * function to get user token for a application
	 */
	function getUserToken($userId, $category = 'google') {
		$category = addslashes($category);
		$userId = intval($userId);
		$whereCond = "user_id=$userId and token_category='$category' order by created DESC";
		$tokenInfo = $this->dbHelper->getRow("user_tokens", $whereCond);
		return $this->__decryptTokenFields($tokenInfo);
	}

	/*
	 * function to insert user token
	 */
	function insertUserToken($tokenInfo) {
		$ret = $this->dbHelper->insertRow("user_tokens", $this->__encryptTokenFields($tokenInfo));
		return $ret;
	}

	/*
	 * function to update user token
	 */
	function updateUserToken($tokenId, $tokenInfo) {
		$whereCond = "id=" . intval($tokenId);
		$ret = $this->dbHelper->updateRow("user_tokens", $this->__encryptTokenFields($tokenInfo), $whereCond);
		return $ret;
	}

	function __encryptTokenFields($tokenInfo) {
		foreach ($this->encryptedFields as $field) {
			if (!empty($tokenInfo[$field])) {
				$tokenInfo[$field] = $this->__encryptTokenValue($tokenInfo[$field]);
			}
		}
		return $tokenInfo;
	}

	function __decryptTokenFields($tokenInfo) {
		if (empty($tokenInfo)) return $tokenInfo;
		foreach ($this->encryptedFields as $field) {
			if (!empty($tokenInfo[$field])) {
				$tokenInfo[$field] = $this->__decryptTokenValue($tokenInfo[$field]);
			}
		}
		return $tokenInfo;
	}

	// resolves (generating + persisting on first use if necessary) the
	// symmetric key used to encrypt access_token/refresh_token at rest.
	// Lives in settings, same as this codebase's other generated secrets
	// (SP_API_KEY/API_SECRET) - generated lazily the first time a token
	// is actually stored, so an existing install upgrading to this fix
	// needs no migration step; it just gets a key created the first time
	// someone (re)connects Google Analytics/Search Console.
	function __getTokenEncryptionKey() {
		$keyInfo = $this->db->select("select set_val from settings where set_name='SP_TOKEN_ENCRYPTION_KEY'", true);
		if (!empty($keyInfo['set_val'])) {
			return base64_decode($keyInfo['set_val']);
		}

		$key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
		$encoded = addslashes(base64_encode($key));
		if (!empty($keyInfo['id']) || $this->db->select("select id from settings where set_name='SP_TOKEN_ENCRYPTION_KEY'", true)) {
			$this->db->query("update settings set set_val='$encoded' where set_name='SP_TOKEN_ENCRYPTION_KEY'");
		} else {
			$this->db->query("insert into settings(set_label,set_name,set_val,set_type) values('Token Encryption Key','SP_TOKEN_ENCRYPTION_KEY','$encoded','large')");
		}
		return $key;
	}

	// "sbx:"-prefixed so __decryptTokenValue() can tell an already-
	// encrypted value apart from a legacy plaintext token already in the
	// DB from before this fix - those keep working as-is (returned
	// unchanged) until they're naturally re-issued/refreshed, at which
	// point they get encrypted too. No forced re-auth needed on upgrade.
	function __encryptTokenValue($plaintext) {
		$key = $this->__getTokenEncryptionKey();
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$cipher = sodium_crypto_secretbox($plaintext, $nonce, $key);
		return 'sbx:' . base64_encode($nonce . $cipher);
	}

	function __decryptTokenValue($stored) {
		if (strpos((string) $stored, 'sbx:') !== 0) {
			return $stored;
		}
		$raw = base64_decode(substr($stored, 4));
		$nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$key = $this->__getTokenEncryptionKey();
		$plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
		return $plain !== false ? $plain : '';
	}
	
	/*
	 * function to delete user token
	 */
	function deleteToken($tokenId) {
		$whereCond = "id=" . intval($tokenId);
		$ret = $this->dbHelper->deleteRows("user_tokens", $whereCond);
		return $ret;
	}
	
	/*
	 * function to delete all user token
	 */
	function deleteAllUserTokens($userId, $category = 'google') {
		$category = addslashes($category);
		$userId = intval($userId);
		$whereCond = "user_id=$userId and token_category='$category'";
		$ret = $this->dbHelper->deleteRows("user_tokens", $whereCond);
		return $ret;
	}
	
}
?>