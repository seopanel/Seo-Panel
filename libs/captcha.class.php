<?php
/***************************************************************/
/* PhpCaptcha - A visual and audio CAPTCHA generation library

   Software License Agreement (BSD License)

   Copyright (C) 2005-2024, Edward Eliot & Contributors.
   All rights reserved.

   Redistribution and use in source and binary forms, with or without
   modification, are permitted provided that the following conditions are met:

      * Redistributions of source code must retain the above copyright
        notice, this list of conditions and the following disclaimer.
      * Redistributions in binary form must reproduce the above copyright
        notice, this list of conditions and the following disclaimer in the
        documentation and/or other materials provided with the distribution.
      * Neither the name of Edward Eliot nor the names of its contributors
        may be used to endorse or promote products derived from this software
        without specific prior written permission of Edward Eliot.

   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
   EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
   DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
   DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
   (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
   LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
   ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
   (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
   SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

   Last Updated: 2024 - Modernized for PHP 8.x compatibility */
/***************************************************************/

/************************ Default Options **********************/

// Start a PHP session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Class defaults - change to effect globally
define('CAPTCHA_SESSION_ID', 'php_captcha');
define('CAPTCHA_WIDTH', 200); // max 500
define('CAPTCHA_HEIGHT', 50); // max 200
define('CAPTCHA_NUM_CHARS', 5);
define('CAPTCHA_NUM_LINES', 70);
define('CAPTCHA_CHAR_SHADOW', false);
define('CAPTCHA_OWNER_TEXT', '');
define('CAPTCHA_CHAR_SET', ''); // defaults to A-Z
define('CAPTCHA_CASE_INSENSITIVE', true);
define('CAPTCHA_BACKGROUND_IMAGES', '');
define('CAPTCHA_MIN_FONT_SIZE', 16);
define('CAPTCHA_MAX_FONT_SIZE', 25);
define('CAPTCHA_USE_COLOUR', false);
define('CAPTCHA_FILE_TYPE', 'png');
define('CAPTCHA_FLITE_PATH', '/usr/bin/flite');
define('CAPTCHA_AUDIO_PATH', '/tmp/'); // must be writeable by PHP process

/************************ End Default Options **********************/

/**
 * PhpCaptcha - Modern CAPTCHA generation class
 *
 * @package PhpCaptcha
 * @version 2.0
 */
class PhpCaptcha {
    protected $oImage;
    protected array $aFonts = [];
    protected int $iWidth = 0;
    protected int $iHeight = 0;
    protected int $iNumChars = 0;
    protected int $iNumLines = 0;
    protected int $iSpacing = 0;
    protected bool $bCharShadow = false;
    protected string $sOwnerText = '';
    protected array $aCharSet = [];
    protected bool $bCaseInsensitive = true;
    protected $vBackgroundImages = '';
    protected int $iMinFontSize = 0;
    protected int $iMaxFontSize = 0;
    protected bool $bUseColour = false;
    protected string $sFileType = '';
    protected string $sCode = '';

    /**
     * Constructor
     *
     * @param array $aFonts Array of TrueType fonts to use - specify full path
     * @param int $iWidth Width of image
     * @param int $iHeight Height of image
     */
    public function __construct(
        array $aFonts,
        int $iWidth = CAPTCHA_WIDTH,
        int $iHeight = CAPTCHA_HEIGHT
    ) {
        $this->aFonts = $aFonts;
        $this->SetNumChars(CAPTCHA_NUM_CHARS);
        $this->SetNumLines(CAPTCHA_NUM_LINES);
        $this->DisplayShadow(CAPTCHA_CHAR_SHADOW);
        $this->SetOwnerText(CAPTCHA_OWNER_TEXT);
        $this->SetCharSet(CAPTCHA_CHAR_SET);
        $this->CaseInsensitive(CAPTCHA_CASE_INSENSITIVE);
        $this->SetBackgroundImages(CAPTCHA_BACKGROUND_IMAGES);
        $this->SetMinFontSize(CAPTCHA_MIN_FONT_SIZE);
        $this->SetMaxFontSize(CAPTCHA_MAX_FONT_SIZE);
        $this->UseColour(CAPTCHA_USE_COLOUR);
        $this->SetFileType(CAPTCHA_FILE_TYPE);
        $this->SetWidth($iWidth);
        $this->SetHeight($iHeight);
    }

    /**
     * Calculate spacing between characters
     */
    protected function CalculateSpacing(): void {
        $this->iSpacing = (int)($this->iWidth / $this->iNumChars);
    }

    /**
     * Set image width
     *
     * @param int $iWidth Width in pixels
     */
    public function SetWidth(int $iWidth): void {
        $this->iWidth = $iWidth;
        if ($this->iWidth > 500) {
            $this->iWidth = 500; // to prevent performance impact
        }
        $this->CalculateSpacing();
    }

    /**
     * Set image height
     *
     * @param int $iHeight Height in pixels
     */
    public function SetHeight(int $iHeight): void {
        $this->iHeight = $iHeight;
        if ($this->iHeight > 200) {
            $this->iHeight = 200; // to prevent performance impact
        }
    }

    /**
     * Set number of characters
     *
     * @param int $iNumChars Number of characters to display
     */
    public function SetNumChars(int $iNumChars): void {
        $this->iNumChars = $iNumChars;
        $this->CalculateSpacing();
    }

    /**
     * Set number of distortion lines
     *
     * @param int $iNumLines Number of lines
     */
    public function SetNumLines(int $iNumLines): void {
        $this->iNumLines = $iNumLines;
    }

    /**
     * Enable/disable character shadow
     *
     * @param bool $bCharShadow Enable shadow
     */
    public function DisplayShadow(bool $bCharShadow): void {
        $this->bCharShadow = $bCharShadow;
    }

    /**
     * Set owner text at bottom
     *
     * @param string $sOwnerText Owner text
     */
    public function SetOwnerText(string $sOwnerText): void {
        $this->sOwnerText = $sOwnerText;
    }

    /**
     * Set character set
     *
     * @param string|array $vCharSet Character set
     */
    public function SetCharSet($vCharSet): void {
        if (is_array($vCharSet)) {
            $this->aCharSet = $vCharSet;
        } else {
            if ($vCharSet != '') {
                $aCharSet = explode(',', $vCharSet);
                $this->aCharSet = array();

                foreach ($aCharSet as $sCurrentItem) {
                    if (strlen($sCurrentItem) == 3) {
                        $aRange = explode('-', $sCurrentItem);

                        if (count($aRange) == 2 && $aRange[0] < $aRange[1]) {
                            $aRange = range($aRange[0], $aRange[1]);
                            $this->aCharSet = array_merge($this->aCharSet, $aRange);
                        }
                    } else {
                        $this->aCharSet[] = $sCurrentItem;
                    }
                }
            } else {
                $this->aCharSet = array();
            }
        }
    }

    /**
     * Enable/disable case insensitive validation
     *
     * @param bool $bCaseInsensitive Case insensitive
     */
    public function CaseInsensitive(bool $bCaseInsensitive): void {
        $this->bCaseInsensitive = $bCaseInsensitive;
    }

    /**
     * Set background images
     *
     * @param string|array $vBackgroundImages Background image(s)
     */
    public function SetBackgroundImages($vBackgroundImages): void {
        $this->vBackgroundImages = $vBackgroundImages;
    }

    /**
     * Set minimum font size
     *
     * @param int $iMinFontSize Minimum font size
     */
    public function SetMinFontSize(int $iMinFontSize): void {
        $this->iMinFontSize = $iMinFontSize;
    }

    /**
     * Set maximum font size
     *
     * @param int $iMaxFontSize Maximum font size
     */
    public function SetMaxFontSize(int $iMaxFontSize): void {
        $this->iMaxFontSize = $iMaxFontSize;
    }

    /**
     * Enable/disable color
     *
     * @param bool $bUseColour Use color
     */
    public function UseColour(bool $bUseColour): void {
        $this->bUseColour = $bUseColour;
    }

    /**
     * Set output file type
     *
     * @param string $sFileType File type (gif, png, jpeg)
     */
    public function SetFileType(string $sFileType): void {
        if (in_array($sFileType, array('gif', 'png', 'jpeg'))) {
            $this->sFileType = $sFileType;
        } else {
            $this->sFileType = 'png';
        }
    }

    /**
     * Draw distortion lines
     */
    protected function DrawLines(): void {
        for ($i = 0; $i < $this->iNumLines; $i++) {
            if ($this->bUseColour) {
                $iLineColour = imagecolorallocate(
                    $this->oImage,
                    random_int(100, 250),
                    random_int(100, 250),
                    random_int(100, 250)
                );
            } else {
                $iRandColour = random_int(100, 250);
                $iLineColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
            }

            imageline(
                $this->oImage,
                random_int(0, $this->iWidth),
                random_int(0, $this->iHeight),
                random_int(0, $this->iWidth),
                random_int(0, $this->iHeight),
                $iLineColour
            );
        }
    }

    /**
     * Draw owner text at bottom
     */
    protected function DrawOwnerText(): void {
        $iBlack = imagecolorallocate($this->oImage, 0, 0, 0);
        $iOwnerTextHeight = imagefontheight(2);
        $iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;

        imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);
        imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);

        $this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
    }

    /**
     * Generate random code
     */
    protected function GenerateCode(): void {
        $this->sCode = '';

        for ($i = 0; $i < $this->iNumChars; $i++) {
            if (count($this->aCharSet) > 0) {
                $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
            } else {
                $this->sCode .= chr(random_int(65, 90));
            }
        }

        $_SESSION[CAPTCHA_SESSION_ID] = $this->bCaseInsensitive
            ? strtoupper($this->sCode)
            : $this->sCode;
    }

    /**
     * Draw characters on image
     */
    protected function DrawCharacters(): void {
        for ($i = 0; $i < strlen($this->sCode); $i++) {
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];

            if ($this->bUseColour) {
                $iTextColour = imagecolorallocate(
                    $this->oImage,
                    random_int(0, 100),
                    random_int(0, 100),
                    random_int(0, 100)
                );

                if ($this->bCharShadow) {
                    $iShadowColour = imagecolorallocate(
                        $this->oImage,
                        random_int(0, 100),
                        random_int(0, 100),
                        random_int(0, 100)
                    );
                }
            } else {
                $iRandColour = random_int(0, 100);
                $iTextColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);

                if ($this->bCharShadow) {
                    $iRandColour = random_int(0, 100);
                    $iShadowColour = imagecolorallocate($this->oImage, $iRandColour, $iRandColour, $iRandColour);
                }
            }

            $iFontSize = random_int($this->iMinFontSize, $this->iMaxFontSize);
            $iAngle = random_int(-30, 30);

            $aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $this->sCode[$i], array());

            $iX = (int)($this->iSpacing / 4 + $i * $this->iSpacing);
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = (int)($this->iHeight / 2 + $iCharHeight / 4);

            imagefttext($this->oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColour, $sCurrentFont, $this->sCode[$i], array());

            if ($this->bCharShadow && isset($iShadowColour)) {
                $iOffsetAngle = random_int(-30, 30);
                $iRandOffsetX = random_int(-5, 5);
                $iRandOffsetY = random_int(-5, 5);

                imagefttext(
                    $this->oImage,
                    $iFontSize,
                    $iOffsetAngle,
                    $iX + $iRandOffsetX,
                    $iY + $iRandOffsetY,
                    $iShadowColour,
                    $sCurrentFont,
                    $this->sCode[$i],
                    array()
                );
            }
        }
    }

    /**
     * Write image to file or output to browser
     *
     * @param string $sFilename Filename or empty for browser output
     */
    protected function WriteFile(string $sFilename = ''): void {
        if ($sFilename == '') {
            header("Content-type: image/{$this->sFileType}");
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
        }

        switch ($this->sFileType) {
            case 'gif':
                $sFilename != '' ? imagegif($this->oImage, $sFilename) : imagegif($this->oImage);
                break;
            case 'png':
                $sFilename != '' ? imagepng($this->oImage, $sFilename) : imagepng($this->oImage);
                break;
            default:
                $sFilename != '' ? imagejpeg($this->oImage, $sFilename, 90) : imagejpeg($this->oImage, null, 90);
        }
    }

    /**
     * Create CAPTCHA image
     *
     * @param string $sFilename Filename or empty for browser output
     * @return bool Success status
     */
    public function Create(string $sFilename = ''): bool {
        // Check for required GD functions
        if (!function_exists('imagecreate') ||
            !function_exists("image{$this->sFileType}") ||
            ($this->vBackgroundImages != '' && !function_exists('imagecreatetruecolor'))) {
            return false;
        }

        // Get background image if specified
        if (is_array($this->vBackgroundImages) || $this->vBackgroundImages != '') {
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);

            if (is_array($this->vBackgroundImages)) {
                $iRandImage = array_rand($this->vBackgroundImages);
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages[$iRandImage]);
            } else {
                $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages);
            }

            imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);
            imagedestroy($oBackgroundImage);
        } else {
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
        }

        // Allocate white background
        imagecolorallocate($this->oImage, 255, 255, 255);

        if ($this->sOwnerText != '') {
            $this->DrawOwnerText();
        }

        if (!is_array($this->vBackgroundImages) && $this->vBackgroundImages == '') {
            $this->DrawLines();
        }

        $this->GenerateCode();
        $this->DrawCharacters();

        $this->WriteFile($sFilename);
        imagedestroy($this->oImage);

        return true;
    }

    /**
     * Validate user input against stored CAPTCHA
     *
     * @param string $sUserCode User input
     * @param bool $bCaseInsensitive Case insensitive validation
     * @return bool Validation result
     */
    public static function Validate(string $sUserCode, bool $bCaseInsensitive = true): bool {
        if ($bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
        }

        if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $sUserCode == $_SESSION[CAPTCHA_SESSION_ID]) {
            // Clear to prevent re-use
            unset($_SESSION[CAPTCHA_SESSION_ID]);
            return true;
        }

        return false;
    }

    /**
     * Get the current CAPTCHA code (for debugging only)
     *
     * @return string Current code
     */
    public function GetCode(): string {
        return $this->sCode;
    }
}

/**
 * AudioPhpCaptcha - Audio CAPTCHA generation class
 *
 * This class will only work correctly if a visual CAPTCHA has been created first
 *
 * @package PhpCaptcha
 * @version 2.0
 */
class AudioPhpCaptcha {
    protected string $sFlitePath = '';
    protected string $sAudioPath = '';
    protected string $sCode = '';

    /**
     * Constructor
     *
     * @param string $sFlitePath Path to flite binary
     * @param string $sAudioPath Path to store temporary audio files
     */
    public function __construct(
        string $sFlitePath = CAPTCHA_FLITE_PATH,
        string $sAudioPath = CAPTCHA_AUDIO_PATH
    ) {
        $this->SetFlitePath($sFlitePath);
        $this->SetAudioPath($sAudioPath);

        if (isset($_SESSION[CAPTCHA_SESSION_ID])) {
            $this->sCode = $_SESSION[CAPTCHA_SESSION_ID];
        } else {
            $this->sCode = '';
        }
    }

    /**
     * Set flite binary path
     *
     * @param string $sFlitePath Path to flite
     */
    public function SetFlitePath(string $sFlitePath): void {
        $this->sFlitePath = $sFlitePath;
    }

    /**
     * Set audio storage path
     *
     * @param string $sAudioPath Storage path
     */
    public function SetAudioPath(string $sAudioPath): void {
        $this->sAudioPath = $sAudioPath;
    }

    /**
     * Format text for audio output
     *
     * @param string $sText Text to format
     * @return string Formatted text
     */
    protected function Mask(string $sText): string {
        $iLength = strlen($sText);
        $sFormattedText = '';

        for ($i = 0; $i < $iLength; $i++) {
            if ($i > 0 && $i < $iLength - 1) {
                $sFormattedText .= ', ';
            } elseif ($i == $iLength - 1) {
                $sFormattedText .= ' and ';
            }
            $sFormattedText .= $sText[$i];
        }

        $aPhrases = array(
            "The %1\$s characters are as follows: %2\$s",
            "%2\$s, are the %1\$s letters",
            "Here are the %1\$s characters: %2\$s",
            "%1\$s characters are: %2\$s",
            "%1\$s letters: %2\$s"
        );

        $iPhrase = array_rand($aPhrases);

        return sprintf($aPhrases[$iPhrase], $iLength, $sFormattedText);
    }

    /**
     * Create audio CAPTCHA
     *
     * @return bool Success status
     */
    public function Create(): bool {
        if (empty($this->sCode)) {
            return false;
        }

        $sText = $this->Mask($this->sCode);
        $sFile = md5($this->sCode . time());

        // Create file with flite
        $sCommand = escapeshellcmd($this->sFlitePath) . ' -t ' .
                    escapeshellarg($sText) . ' -o ' .
                    escapeshellarg($this->sAudioPath . $sFile . '.wav');
        shell_exec($sCommand);

        if (!file_exists($this->sAudioPath . $sFile . '.wav')) {
            return false;
        }

        // Set headers
        header('Content-type: audio/x-wav');
        header("Content-Disposition: attachment;filename={$sFile}.wav");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Output to browser
        echo file_get_contents($this->sAudioPath . $sFile . '.wav');

        // Delete temporary file
        @unlink($this->sAudioPath . $sFile . '.wav');

        return true;
    }
}

/**
 * PhpCaptchaColour - Colored CAPTCHA variant
 *
 * @package PhpCaptcha
 * @version 2.0
 */
class PhpCaptchaColour extends PhpCaptcha {
    /**
     * Constructor
     *
     * @param array $aFonts Array of fonts
     * @param int $iWidth Image width
     * @param int $iHeight Image height
     */
    public function __construct(array $aFonts, int $iWidth = CAPTCHA_WIDTH, int $iHeight = CAPTCHA_HEIGHT) {
        parent::__construct($aFonts, $iWidth, $iHeight);
        $this->UseColour(true);
    }
}
