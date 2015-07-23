<?php
/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage.php<br />
 *
 * Copyright (c) 2012, Drew Phillips
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2012 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 3.2RC2 (April 2012)
 * @package Securimage
 *
 */
/**
 * Securimage CAPTCHA Class.
 *
 * @version    3.0
 * @package    Securimage
 * @subpackage classes
 * @author     Drew Phillips <drew@drew-phillips.com>
 *
 */
class Securimage
{
    // All of the public variables below are securimage options
    // They can be passed as an array to the Securimage constructor, set below,
    // or set from securimage_show.php and securimage_play.php

    /**
     * Renders captcha as a JPEG image
     * @var int
     */
    const SI_IMAGE_JPEG = 1;
    /**
     * Renders captcha as a PNG image (default)
     * @var int
     */
    const SI_IMAGE_PNG  = 2;
    /**
     * Renders captcha as a GIF image
     * @var int
     */
    const SI_IMAGE_GIF  = 3;

    /**
     * Create a normal alphanumeric captcha
     * @var int
     */
    const SI_CAPTCHA_STRING     = 0;
    /**
     * Create a captcha consisting of a simple math problem
     * @var int
     */
    const SI_CAPTCHA_MATHEMATIC = 1;

    /**
     * The width of the captcha image
     * @var int
     */
    public $image_width = 215;
    /**
     * The height of the captcha image
     * @var int
     */
    public $image_height = 80;
    /**
     * The type of the image, default = png
     * @var int
     */
    public $image_type   = self::SI_IMAGE_PNG;

    /**
     * The background color of the captcha
     * @var Securimage_Color
     */
    public $image_bg_color = '#ffffff';
    /**
     * The color of the captcha text
     * @var Securimage_Color
     */
    public $text_color     = '#707070';
    /**
     * The color of the lines over the captcha
     * @var Securimage_Color
     */
    public $line_color     = '#707070';
    /**
     * The color of the noise that is drawn
     * @var Securimage_Color
     */
    public $noise_color    = '#707070';

    /**
     * How transparent to make the text 0 = completely opaque, 100 = invisible
     * @var int
     */
    public $text_transparency_percentage = 50;
    /**
     * Whether or not to draw the text transparently, true = use transparency, false = no transparency
     * @var bool
     */
    public $use_transparent_text         = false;

    /**
     * The length of the captcha code
     * @var int
     */
    public $code_length    = 6;
    /**
     * Whether the captcha should be case sensitive (not recommended, use only for maximum protection)
     * @var bool
     */
    public $case_sensitive = false;
    /**
     * The character set to use for generating the captcha code
     * @var string
     */
    public $charset        = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';
    /**
     * How long in seconds a captcha remains valid, after this time it will not be accepted
     * @var unknown_type
     */
    public $expiry_time    = 900;

    /**
     * The session name securimage should use, only set this if your application uses a custom session name
     * It is recommended to set this value below so it is used by all securimage scripts
     * @var string
     */
    public $session_name   = null;

    /**
     * true to use the wordlist file, false to generate random captcha codes
     * @var bool
     */
    public $use_wordlist   = false;

    /**
     * The level of distortion, 0.75 = normal, 1.0 = very high distortion
     * @var double
     */
    public $perturbation = 0.75;
    /**
     * How many lines to draw over the captcha code to increase security
     * @var int
     */
    public $num_lines    = 8;
    /**
     * The level of noise (random dots) to place on the image, 0-10
     * @var int
     */
    public $noise_level  = 0;

    /**
     * The signature text to draw on the bottom corner of the image
     * @var string
     */
    public $image_signature = '';
    /**
     * The color of the signature text
     * @var Securimage_Color
     */
    public $signature_color = '#707070';
    /**
     * The path to the ttf font file to use for the signature text, defaults to $ttf_file (AHGBold.ttf)
     * @var string
     */
    public $signature_font;

    /**
     * Use an SQLite database to store data (for users that do not support cookies)
     * @var bool
     */
    public $use_sqlite_db = false;

    /**
     * The type of captcha to create, either alphanumeric, or a math problem<br />
     * Securimage::SI_CAPTCHA_STRING or Securimage::SI_CAPTCHA_MATHEMATIC
     * @var int
     */
    public $captcha_type  = self::SI_CAPTCHA_STRING; // or self::SI_CAPTCHA_MATHEMATIC;

    /**
     * The captcha namespace, use this if you have multiple forms on a single page, blank if you do not use multiple forms on one page
     * @var string
     * <code>
     * <?php
     * // in securimage_show.php (create one show script for each form)
     * $img->namespace = 'contact_form';
     *
     * // in form validator
     * $img->namespace = 'contact_form';
     * if ($img->check($code) == true) {
     *     echo "Valid!";
     *  }
     * </code>
     */
    public $namespace;

    /**
     * The font file to use to draw the captcha code, leave blank for default font AHGBold.ttf
     * @var string
     */
    public $ttf_file;
    /**
     * The path to the wordlist file to use, leave blank for default words/words.txt
     * @var string
     */
    public $wordlist_file;
    /**
     * The directory to scan for background images, if set a random background will be chosen from this folder
     * @var string
     */
    public $background_directory;
    /**
     * The path to the SQLite database file to use, if $use_sqlite_database = true, should be chmod 666
     * @var string
     */
    public $sqlite_database;
    /**
     * The path to the securimage audio directory, can be set in securimage_play.php
     * @var string
     * <code>
     * $img->audio_path = '/home/yoursite/public_html/securimage/audio/';
     * </code>
     */
    public $audio_path;
    /**
     * The path to the directory containing audio files that will be selected
     * randomly and mixed with the captcha audio.
     *
     * @var string
     */
    public $audio_noise_path;
    /**
     * Whether or not to mix background noise files into captcha audio (true = mix, false = no)
     * Mixing random background audio with noise can help improve security of audio captcha.
     * Default: securimage/audio/noise
     *
     * @since 3.0.3
     * @see Securimage::$audio_noise_path
     * @var bool
     */
    public $audio_use_noise;
    /**
     * The method and threshold (or gain factor) used to normalize the mixing with background noise.
     * See http://www.voegler.eu/pub/audio/ for more information.
     *
     * Valid: <ul>
     *     <li> >= 1 - Normalize by multiplying by the threshold (boost - positive gain). <br />
     *            A value of 1 in effect means no normalization (and results in clipping). </li>
     *     <li> <= -1 - Normalize by dividing by the the absolute value of threshold (attenuate - negative gain). <br />
     *            A factor of 2 (-2) is about 6dB reduction in volume.</li>
     *     <li> [0, 1) - (open inverval - not including 1) - The threshold
     *            above which amplitudes are comressed logarithmically. <br />
     *            e.g. 0.6 to leave amplitudes up to 60% "as is" and compress above. </li>
     *     <li> (-1, 0) - (open inverval - not including -1 and 0) - The threshold
     *            above which amplitudes are comressed linearly. <br />
     *            e.g. -0.6 to leave amplitudes up to 60% "as is" and compress above. </li></ul>
     *
     * Default: 0.6
     *
     * @since 3.0.4
     * @var float
     */
    public $audio_mix_normalization = 0.6;
    /**
     * Whether or not to degrade audio by introducing random noise (improves security of audio captcha)
     * Default: true
     *
     * @since 3.0.3
     * @var bool
     */
    public $degrade_audio;
    /**
     * Minimum delay to insert between captcha audio letters in milliseconds
     *
     * @since 3.0.3
     * @var float
     */
    public $audio_gap_min = 0;
    /**
     * Maximum delay to insert between captcha audio letters in milliseconds
     *
     * @since 3.0.3
     * @var float
     */
    public $audio_gap_max = 600;

    /**
     * Captcha ID if using static captcha
     * @var string Unique captcha id
     */
    protected static $_captchaId = null;

    protected $im;
    protected $tmpimg;
    protected $bgimg;
    protected $iscale = 5;

    protected $securimage_path = null;

    /**
     * The captcha challenge value (either the case-sensitive/insensitive word captcha, or the solution to the math captcha)
     *
     * @var string Captcha challenge value
     */
    protected $code;

    /**
     * The display value of the captcha to draw on the image (the word captcha, or the math equation to present to the user)
     *
     * @var string Captcha display value to draw on the image
     */
    protected $code_display;

    /**
     * A value that can be passed to the constructor that can be used to generate a captcha image with a given value
     * This value does not get stored in the session or database and is only used when calling Securimage::show().
     * If a display_value was passed to the constructor and the captcha image is generated, the display_value will be used
     * as the string to draw on the captcha image.  Used only if captcha codes are generated and managed by a 3rd party app/library
     *
     * @var string Captcha code value to display on the image
     */
    protected $display_value;

    /**
     * Captcha code supplied by user [set from Securimage::check()]
     *
     * @var string
     */
    protected $captcha_code;

    /**
     * Flag that can be specified telling securimage not to call exit after generating a captcha image or audio file
     *
     * @var bool If true, script will not terminate; if false script will terminate (default)
     */
    protected $no_exit;

    /**
     * Flag indicating whether or not a PHP session should be started and used
     *
     * @var bool If true, no session will be started; if false, session will be started and used to store data (default)
     */
    protected $no_session;

    /**
     * Flag indicating whether or not HTTP headers will be sent when outputting captcha image/audio
     *
     * @var bool If true (default) headers will be sent, if false, no headers are sent
     */
    protected $send_headers;

    // sqlite database handle (if using sqlite)
    protected $sqlite_handle;

    // gd color resources that are allocated for drawing the image
    protected $gdbgcolor;
    protected $gdtextcolor;
    protected $gdlinecolor;
    protected $gdsignaturecolor;

    /**
     * Create a new securimage object, pass options to set in the constructor.<br />
     * This can be used to display a captcha, play an audible captcha, or validate an entry
     * @param array $options
     * <code>
     * $options = array(
     *     'text_color' => new Securimage_Color('#013020'),
     *     'code_length' => 5,
     *     'num_lines' => 5,
     *     'noise_level' => 3,
     *     'font_file' => Securimage::getPath() . '/custom.ttf'
     * );
     *
     * $img = new Securimage($options);
     * </code>
     */
    public function __construct($options = array())
    {
        $this->securimage_path = dirname(__FILE__);

        if (is_array($options) && sizeof($options) > 0) {
            foreach($options as $prop => $val) {
                if ($prop == 'captchaId') {
                    Securimage::$_captchaId = $val;
                    $this->use_sqlite_db    = true;
                } else {
                    $this->$prop = $val;
                }
            }
        }

        $this->image_bg_color  = $this->initColor($this->image_bg_color,  '#ffffff');
        $this->text_color      = $this->initColor($this->text_color,      '#616161');
        $this->line_color      = $this->initColor($this->line_color,      '#616161');
        $this->noise_color     = $this->initColor($this->noise_color,     '#616161');
        $this->signature_color = $this->initColor($this->signature_color, '#616161');

        if (is_null($this->ttf_file)) {
            $this->ttf_file = $this->securimage_path . '/font/AHGBold.ttf';
        }

        $this->signature_font = $this->ttf_file;

        if (is_null($this->wordlist_file)) {
            $this->wordlist_file = $this->securimage_path . '/words/words.txt';
        }

        if (is_null($this->sqlite_database)) {
            $this->sqlite_database = $this->securimage_path . '/database/securimage.sqlite';
        }

        if (is_null($this->audio_path)) {
            $this->audio_path = $this->securimage_path . '/audio/';
        }

        if (is_null($this->audio_noise_path)) {
            $this->audio_noise_path = $this->audio_path . '/noise/';
        }

        if (is_null($this->audio_use_noise)) {
            $this->audio_use_noise = true;
        }

        if (is_null($this->degrade_audio)) {
            $this->degrade_audio = true;
        }

        if (is_null($this->code_length) || (int)$this->code_length < 1) {
            $this->code_length = 6;
        }

        if (is_null($this->perturbation) || !is_numeric($this->perturbation)) {
            $this->perturbation = 0.75;
        }

        if (is_null($this->namespace) || !is_string($this->namespace)) {
            $this->namespace = 'default';
        }

        if (is_null($this->no_exit)) {
            $this->no_exit = false;
        }

        if (is_null($this->no_session)) {
            $this->no_session = false;
        }

        if (is_null($this->send_headers)) {
            $this->send_headers = true;
        }

        if ($this->no_session != true) {
            // Initialize session or attach to existing
            if ( session_id() == '' ) { // no session has been started yet, which is needed for validation
                if (!is_null($this->session_name) && trim($this->session_name) != '') {
                    session_name(trim($this->session_name)); // set session name if provided
                }
                session_start();
            }
        }
    }

    /**
     * Return the absolute path to the Securimage directory
     * @return string The path to the securimage base directory
     */
    public static function getPath()
    {
        return dirname(__FILE__);
    }

    /**
     * Generate a new captcha ID or retrieve the current ID
     *
     * @param $new bool If true, generates a new challenge and returns and ID
     * @param $options array Additional options to be passed to Securimage
     *
     * @return null|string Returns null if no captcha id set and new was false, or string captcha ID
     */
    public static function getCaptchaId($new = true, array $options = array())
    {
        if ((bool)$new == true) {
            $id = sha1(uniqid($_SERVER['REMOTE_ADDR'], true));
            $opts = array('no_session'    => true,
                          'use_sqlite_db' => true);
            if (sizeof($options) > 0) $opts = array_merge($opts, $options);
            $si = new self($opts);
            Securimage::$_captchaId = $id;
            $si->createCode();

            return $id;
        } else {
            return Securimage::$_captchaId;
        }
    }

    /**
     * Validate a captcha code input against a captcha ID
     * @param string $id The captcha ID to check
     * @param string $value The captcha value supplied by the user
     *
     * @return bool true if the code was valid for the given captcha ID, false if not or if database failed to open
     */
    public static function checkByCaptchaId($id, $value)
    {
        $si = new self(array('captchaId'     => $id,
                             'no_session'    => true,
                             'use_sqlite_db' => true));

        if ($si->openDatabase()) {
            $code = $si->getCodeFromDatabase();

            if (is_array($code)) {
                $si->code         = $code['code'];
                $si->code_display = $code['code_disp'];
            }

            if ($si->check($value)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * Used to serve a captcha image to the browser
     * @param string $background_image The path to the background image to use
     * <code>
     * $img = new Securimage();
     * $img->code_length = 6;
     * $img->num_lines   = 5;
     * $img->noise_level = 5;
     *
     * $img->show(); // sends the image to browser
     * exit;
     * </code>
     */
    public function show($background_image = '')
    {
        set_error_handler(array(&$this, 'errorHandler'));

        if($background_image != '' && is_readable($background_image)) {
            $this->bgimg = $background_image;
        }

        $this->doImage();
    }

    /**
     * Check a submitted code against the stored value
     * @param string $code  The captcha code to check
     * <code>
     * $code = $_POST['code'];
     * $img  = new Securimage();
     * if ($img->check($code) == true) {
     *     $captcha_valid = true;
     * } else {
     *     $captcha_valid = false;
     * }
     * </code>
     */
    public function check($code)
    {
        $this->code_entered = $code;
        $this->validate();
        return $this->correct_code;
    }

    /**
     * Output a wav file of the captcha code to the browser
     *
     * <code>
     * $img = new Securimage();
     * $img->outputAudioFile(); // outputs a wav file to the browser
     * exit;
     * </code>
     */
    public function outputAudioFile()
    {
        set_error_handler(array(&$this, 'errorHandler'));

        try {
            $audio = $this->getAudibleCode();
        } catch (Exception $ex) {
            if (($fp = @fopen(dirname(__FILE__) . '/si.error_log', 'a+')) !== false) {
                fwrite($fp, date('Y-m-d H:i:s') . ': Securimage audio error "' . $ex->getMessage() . '"' . "\n");
                fclose($fp);
            }

            $audio = $this->audioError();
        }

        if ($this->canSendHeaders() || $this->send_headers == false) {
            if ($this->send_headers) {
                $uniq = md5(uniqid(microtime()));
                header("Content-Disposition: attachment; filename=\"securimage_audio-{$uniq}.wav\"");
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
                header('Content-type: audio/x-wav');

                if (extension_loaded('zlib')) {
                    ini_set('zlib.output_compression', true);  // compress output if supported by browser
                } else {
                    header('Content-Length: ' . strlen($audio));
                }
            }

            echo $audio;
        } else {
            echo '<hr /><strong>'
                .'Failed to generate audio file, content has already been '
                .'output.<br />This is most likely due to misconfiguration or '
                .'a PHP error was sent to the browser.</strong>';
        }

        restore_error_handler();

        if (!$this->no_exit) exit;
    }

    /**
     * Return the code from the session or sqlite database if used.  If none exists yet, an empty string is returned
     *
     * @param $array bool   True to receive an array containing the code and properties
     * @return array|string Array if $array = true, otherwise a string containing the code
     */
    public function getCode($array = false)
    {
        $code = '';
        $time = 0;
        $disp = 'error';

        if ($this->no_session != true) {
            if (isset($_SESSION['securimage_code_value'][$this->namespace]) &&
                    trim($_SESSION['securimage_code_value'][$this->namespace]) != '') {
                if ($this->isCodeExpired(
                        $_SESSION['securimage_code_ctime'][$this->namespace]) == false) {
                    $code = $_SESSION['securimage_code_value'][$this->namespace];
                    $time = $_SESSION['securimage_code_ctime'][$this->namespace];
                    $disp = $_SESSION['securimage_code_disp'] [$this->namespace];
                }
            }
        }

        if ($this->use_sqlite_db == true && function_exists('sqlite_open')) {
            // no code in session - may mean user has cookies turned off
            $this->openDatabase();
            $code = $this->getCodeFromDatabase();
        } else { /* no code stored in session or sqlite database, validation will fail */ }

        if ($array == true) {
            return array('code' => $code, 'ctime' => $time, 'display' => $disp);
        } else {
            return $code;
        }
    }

    /**
     * The main image drawing routing, responsible for constructing the entire image and serving it
     */
    protected function doImage()
    {
        if( ($this->use_transparent_text == true || $this->bgimg != '') && function_exists('imagecreatetruecolor')) {
            $imagecreate = 'imagecreatetruecolor';
        } else {
            $imagecreate = 'imagecreate';
        }

        $this->im     = $imagecreate($this->image_width, $this->image_height);
        $this->tmpimg = $imagecreate($this->image_width * $this->iscale, $this->image_height * $this->iscale);

        $this->allocateColors();
        imagepalettecopy($this->tmpimg, $this->im);

        $this->setBackground();

        $code = '';

        if ($this->getCaptchaId(false) !== null) {
            // a captcha Id was supplied

            // check to see if a display_value for the captcha image was set
            if (is_string($this->display_value) && strlen($this->display_value) > 0) {
                $this->code_display = $this->display_value;
                $this->code         = ($this->case_sensitive) ?
                                       $this->display_value   :
                                       strtolower($this->display_value);
                $code = $this->code;
            } else if ($this->openDatabase()) {
                // no display_value, check the database for existing captchaId
                $code = $this->getCodeFromDatabase();

                // got back a result from the database with a valid code for captchaId
                if (is_array($code)) {
                    $this->code         = $code['code'];
                    $this->code_display = $code['code_disp'];
                    $code = $code['code'];
                }
            }
        }

        if ($code == '') {
            // if the code was not set using display_value or was not found in
            // the database, create a new code
            $this->createCode();
        }

        if ($this->noise_level > 0) {
            $this->drawNoise();
        }

        $this->drawWord();

        if ($this->perturbation > 0 && is_readable($this->ttf_file)) {
            $this->distortedCopy();
        }

        if ($this->num_lines > 0) {
            $this->drawLines();
        }

        if (trim($this->image_signature) != '') {
            $this->addSignature();
        }

        $this->output();
    }

    /**
     * Allocate the colors to be used for the image
     */
    protected function allocateColors()
    {
        // allocate bg color first for imagecreate
        $this->gdbgcolor = imagecolorallocate($this->im,
                                              $this->image_bg_color->r,
                                              $this->image_bg_color->g,
                                              $this->image_bg_color->b);

        $alpha = intval($this->text_transparency_percentage / 100 * 127);

        if ($this->use_transparent_text == true) {
            $this->gdtextcolor = imagecolorallocatealpha($this->im,
                                                         $this->text_color->r,
                                                         $this->text_color->g,
                                                         $this->text_color->b,
                                                         $alpha);
            $this->gdlinecolor = imagecolorallocatealpha($this->im,
                                                         $this->line_color->r,
                                                         $this->line_color->g,
                                                         $this->line_color->b,
                                                         $alpha);
            $this->gdnoisecolor = imagecolorallocatealpha($this->im,
                                                          $this->noise_color->r,
                                                          $this->noise_color->g,
                                                          $this->noise_color->b,
                                                          $alpha);
        } else {
            $this->gdtextcolor = imagecolorallocate($this->im,
                                                    $this->text_color->r,
                                                    $this->text_color->g,
                                                    $this->text_color->b);
            $this->gdlinecolor = imagecolorallocate($this->im,
                                                    $this->line_color->r,
                                                    $this->line_color->g,
                                                    $this->line_color->b);
            $this->gdnoisecolor = imagecolorallocate($this->im,
                                                          $this->noise_color->r,
                                                          $this->noise_color->g,
                                                          $this->noise_color->b);
        }

        $this->gdsignaturecolor = imagecolorallocate($this->im,
                                                     $this->signature_color->r,
                                                     $this->signature_color->g,
                                                     $this->signature_color->b);

    }

    /**
     * The the background color, or background image to be used
     */
    protected function setBackground()
    {
        // set background color of image by drawing a rectangle since imagecreatetruecolor doesn't set a bg color
        imagefilledrectangle($this->im, 0, 0,
                             $this->image_width, $this->image_height,
                             $this->gdbgcolor);
        imagefilledrectangle($this->tmpimg, 0, 0,
                             $this->image_width * $this->iscale, $this->image_height * $this->iscale,
                             $this->gdbgcolor);

        if ($this->bgimg == '') {
            if ($this->background_directory != null &&
                is_dir($this->background_directory) &&
                is_readable($this->background_directory))
            {
                $img = $this->getBackgroundFromDirectory();
                if ($img != false) {
                    $this->bgimg = $img;
                }
            }
        }

        if ($this->bgimg == '') {
            return;
        }

        $dat = @getimagesize($this->bgimg);
        if($dat == false) {
            return;
        }

        switch($dat[2]) {
            case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
            case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
            case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
            default: return;
        }

        if(!$newim) return;

        imagecopyresized($this->im, $newim, 0, 0, 0, 0,
                         $this->image_width, $this->image_height,
                         imagesx($newim), imagesy($newim));
    }

    /**
     * Scan the directory for a background image to use
     */
    protected function getBackgroundFromDirectory()
    {
        $images = array();

        if ( ($dh = opendir($this->background_directory)) !== false) {
            while (($file = readdir($dh)) !== false) {
                if (preg_match('/(jpg|gif|png)$/i', $file)) $images[] = $file;
            }

            closedir($dh);

            if (sizeof($images) > 0) {
                return rtrim($this->background_directory, '/') . '/' . $images[rand(0, sizeof($images)-1)];
            }
        }

        return false;
    }

    /**
     * Generates the code or math problem and saves the value to the session
     */
    protected function createCode()
    {
        $this->code = false;

        switch($this->captcha_type) {
            case self::SI_CAPTCHA_MATHEMATIC:
            {
                do {
                    $signs = array('+', '-', 'x');
                    $left  = rand(1, 10);
                    $right = rand(1, 5);
                    $sign  = $signs[rand(0, 2)];

                    switch($sign) {
                        case 'x': $c = $left * $right; break;
                        case '-': $c = $left - $right; break;
                        default:  $c = $left + $right; break;
                    }
                } while ($c <= 0); // no negative #'s or 0

                $this->code         = $c;
                $this->code_display = "$left $sign $right";
                break;
            }

            default:
            {
                if ($this->use_wordlist && is_readable($this->wordlist_file)) {
                    $this->code = $this->readCodeFromFile();
                }

                if ($this->code == false) {
                    $this->code = $this->generateCode($this->code_length);
                }

                $this->code_display = $this->code;
                $this->code         = ($this->case_sensitive) ? $this->code : strtolower($this->code);
            } // default
        }

        $this->saveData();
    }

    /**
     * Draws the captcha code on the image
     */
    protected function drawWord()
    {
        $width2  = $this->image_width * $this->iscale;
        $height2 = $this->image_height * $this->iscale;

        if (!is_readable($this->ttf_file)) {
            imagestring($this->im, 4, 10, ($this->image_height / 2) - 5, 'Failed to load TTF font file!', $this->gdtextcolor);
        } else {
            if ($this->perturbation > 0) {
                $font_size = $height2 * .4;
                $bb = imageftbbox($font_size, 0, $this->ttf_file, $this->code_display);
                $tx = $bb[4] - $bb[0];
                $ty = $bb[5] - $bb[1];
                $x  = floor($width2 / 2 - $tx / 2 - $bb[0]);
                $y  = round($height2 / 2 - $ty / 2 - $bb[1]);

                imagettftext($this->tmpimg, $font_size, 0, $x, $y, $this->gdtextcolor, $this->ttf_file, $this->code_display);
            } else {
                $font_size = $this->image_height * .4;
                $bb = imageftbbox($font_size, 0, $this->ttf_file, $this->code_display);
                $tx = $bb[4] - $bb[0];
                $ty = $bb[5] - $bb[1];
                $x  = floor($this->image_width / 2 - $tx / 2 - $bb[0]);
                $y  = round($this->image_height / 2 - $ty / 2 - $bb[1]);

                imagettftext($this->im, $font_size, 0, $x, $y, $this->gdtextcolor, $this->ttf_file, $this->code_display);
            }
        }

        // DEBUG
        //$this->im = $this->tmpimg;
        //$this->output();

    }

    /**
     * Copies the captcha image to the final image with distortion applied
     */
    protected function distortedCopy()
    {
        $numpoles = 3; // distortion factor
        // make array of poles AKA attractor points
        for ($i = 0; $i < $numpoles; ++ $i) {
            $px[$i]  = rand($this->image_width  * 0.2, $this->image_width  * 0.8);
            $py[$i]  = rand($this->image_height * 0.2, $this->image_height * 0.8);
            $rad[$i] = rand($this->image_height * 0.2, $this->image_height * 0.8);
            $tmp     = ((- $this->frand()) * 0.15) - .15;
            $amp[$i] = $this->perturbation * $tmp;
        }

        $bgCol = imagecolorat($this->tmpimg, 0, 0);
        $width2 = $this->iscale * $this->image_width;
        $height2 = $this->iscale * $this->image_height;
        imagepalettecopy($this->im, $this->tmpimg); // copy palette to final image so text colors come across
        // loop over $img pixels, take pixels from $tmpimg with distortion field
        for ($ix = 0; $ix < $this->image_width; ++ $ix) {
            for ($iy = 0; $iy < $this->image_height; ++ $iy) {
                $x = $ix;
                $y = $iy;
                for ($i = 0; $i < $numpoles; ++ $i) {
                    $dx = $ix - $px[$i];
                    $dy = $iy - $py[$i];
                    if ($dx == 0 && $dy == 0) {
                        continue;
                    }
                    $r = sqrt($dx * $dx + $dy * $dy);
                    if ($r > $rad[$i]) {
                        continue;
                    }
                    $rscale = $amp[$i] * sin(3.14 * $r / $rad[$i]);
                    $x += $dx * $rscale;
                    $y += $dy * $rscale;
                }
                $c = $bgCol;
                $x *= $this->iscale;
                $y *= $this->iscale;
                if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2) {
                    $c = imagecolorat($this->tmpimg, $x, $y);
                }
                if ($c != $bgCol) { // only copy pixels of letters to preserve any background image
                    imagesetpixel($this->im, $ix, $iy, $c);
                }
            }
        }
    }

    /**
     * Draws distorted lines on the image
     */
    protected function drawLines()
    {
        for ($line = 0; $line < $this->num_lines; ++ $line) {
            $x = $this->image_width * (1 + $line) / ($this->num_lines + 1);
            $x += (0.5 - $this->frand()) * $this->image_width / $this->num_lines;
            $y = rand($this->image_height * 0.1, $this->image_height * 0.9);

            $theta = ($this->frand() - 0.5) * M_PI * 0.7;
            $w = $this->image_width;
            $len = rand($w * 0.4, $w * 0.7);
            $lwid = rand(0, 2);

            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos($theta);
            $dy = $step * sin($theta);
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
            $x0 = $x - 0.5 * $len * cos($theta);
            $y0 = $y - 0.5 * $len * sin($theta);

            $ldx = round(- $dy * $lwid);
            $ldy = round($dx * $lwid);

            for ($i = 0; $i < $n; ++ $i) {
                $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                imagefilledrectangle($this->im, $x, $y, $x + $lwid, $y + $lwid, $this->gdlinecolor);
            }
        }
    }

    /**
     * Draws random noise on the image
     */
    protected function drawNoise()
    {
        if ($this->noise_level > 10) {
            $noise_level = 10;
        } else {
            $noise_level = $this->noise_level;
        }

        $t0 = microtime(true);

        $noise_level *= 125; // an arbitrary number that works well on a 1-10 scale

        $points = $this->image_width * $this->image_height * $this->iscale;
        $height = $this->image_height * $this->iscale;
        $width  = $this->image_width * $this->iscale;
        for ($i = 0; $i < $noise_level; ++$i) {
            $x = rand(10, $width);
            $y = rand(10, $height);
            $size = rand(7, 10);
            if ($x - $size <= 0 && $y - $size <= 0) continue; // dont cover 0,0 since it is used by imagedistortedcopy
            imagefilledarc($this->tmpimg, $x, $y, $size, $size, 0, 360, $this->gdnoisecolor, IMG_ARC_PIE);
        }

        $t1 = microtime(true);

        $t = $t1 - $t0;

        /*
        // DEBUG
        imagestring($this->tmpimg, 5, 25, 30, "$t", $this->gdnoisecolor);
        header('content-type: image/png');
        imagepng($this->tmpimg);
        exit;
        */
    }

    /**
    * Print signature text on image
    */
    protected function addSignature()
    {
        $bbox = imagettfbbox(10, 0, $this->signature_font, $this->image_signature);
        $textlen = $bbox[2] - $bbox[0];
        $x = $this->image_width - $textlen - 5;
        $y = $this->image_height - 3;

        imagettftext($this->im, 10, 0, $x, $y, $this->gdsignaturecolor, $this->signature_font, $this->image_signature);
    }

    /**
     * Sends the appropriate image and cache headers and outputs image to the browser
     */
    protected function output()
    {
        if ($this->canSendHeaders() || $this->send_headers == false) {
            if ($this->send_headers) {
                // only send the content-type headers if no headers have been output
                // this will ease debugging on misconfigured servers where warnings
                // may have been output which break the image and prevent easily viewing
                // source to see the error.
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
            }

            switch ($this->image_type) {
                case self::SI_IMAGE_JPEG:
                    if ($this->send_headers) header("Content-Type: image/jpeg");
                    imagejpeg($this->im, null, 90);
                    break;
                case self::SI_IMAGE_GIF:
                    if ($this->send_headers) header("Content-Type: image/gif");
                    imagegif($this->im);
                    break;
                default:
                    if ($this->send_headers) header("Content-Type: image/png");
                    imagepng($this->im);
                    break;
            }
        } else {
            echo '<hr /><strong>'
                .'Failed to generate captcha image, content has already been '
                .'output.<br />This is most likely due to misconfiguration or '
                .'a PHP error was sent to the browser.</strong>';
        }

        imagedestroy($this->im);
        restore_error_handler();

        if (!$this->no_exit) exit;
    }

    /**
     * Gets the code and returns the binary audio file for the stored captcha code
     *
     * @return The audio representation of the captcha in Wav format
     */
    protected function getAudibleCode()
    {
        $letters = array();
        $code    = $this->getCode(true);

        if ($code['code'] == '') {
            $this->createCode();
            $code = $this->getCode(true);
        }

        if (preg_match('/(\d+) (\+|-|x) (\d+)/i', $code['display'], $eq)) {
            $math = true;

            $left  = $eq[1];
            $sign  = str_replace(array('+', '-', 'x'), array('plus', 'minus', 'times'), $eq[2]);
            $right = $eq[3];

            $letters = array($left, $sign, $right);
        } else {
            $math = false;

            $length = strlen($code['display']);

            for($i = 0; $i < $length; ++$i) {
                $letter    = $code['display']{$i};
                $letters[] = $letter;
            }
        }

        try {
            return $this->generateWAV($letters);
        } catch(Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Gets a captcha code from a wordlist
     */
    protected function readCodeFromFile()
    {
        $fp = @fopen($this->wordlist_file, 'rb');
        if (!$fp) return false;

        $fsize = filesize($this->wordlist_file);
        if ($fsize < 128) return false; // too small of a list to be effective

        fseek($fp, rand(0, $fsize - 64), SEEK_SET); // seek to a random position of file from 0 to filesize-64
        $data = fread($fp, 64); // read a chunk from our random position
        fclose($fp);
        $data = preg_replace("/\r?\n/", "\n", $data);

        $start = @strpos($data, "\n", rand(0, 56)) + 1; // random start position
        $end   = @strpos($data, "\n", $start);          // find end of word

        if ($start === false) {
            return false;
        } else if ($end === false) {
            $end = strlen($data);
        }

        return strtolower(substr($data, $start, $end - $start)); // return a line of the file
    }

    /**
     * Generates a random captcha code from the set character set
     */
    protected function generateCode()
    {
        $code = '';

        for($i = 1, $cslen = strlen($this->charset); $i <= $this->code_length; ++$i) {
            $code .= $this->charset{rand(0, $cslen - 1)};
        }

        //return 'testing';  // debug, set the code to given string

        return $code;
    }

    /**
     * Checks the entered code against the value stored in the session or sqlite database, handles case sensitivity
     * Also clears the stored codes if the code was entered correctly to prevent re-use
     */
    protected function validate()
    {
        if (!is_string($this->code) || strlen($this->code) == 0) {
            $code = $this->getCode();
            // returns stored code, or an empty string if no stored code was found
            // checks the session and sqlite database if enabled
        } else {
            $code = $this->code;
        }

        if ($this->case_sensitive == false && preg_match('/[A-Z]/', $code)) {
            // case sensitive was set from securimage_show.php but not in class
            // the code saved in the session has capitals so set case sensitive to true
            $this->case_sensitive = true;
        }

        $code_entered = trim( (($this->case_sensitive) ? $this->code_entered
                                                       : strtolower($this->code_entered))
                        );
        $this->correct_code = false;

        if ($code != '') {
            if ($code == $code_entered) {
                $this->correct_code = true;
                if ($this->no_session != true) {
                    $_SESSION['securimage_code_value'][$this->namespace] = '';
                    $_SESSION['securimage_code_ctime'][$this->namespace] = '';
                }
                $this->clearCodeFromDatabase();
            }
        }
    }

    /**
     * Save data to session namespace and database if used
     */
    protected function saveData()
    {
        if ($this->no_session != true) {
            if (isset($_SESSION['securimage_code_value']) && is_scalar($_SESSION['securimage_code_value'])) {
                // fix for migration from v2 - v3
                unset($_SESSION['securimage_code_value']);
                unset($_SESSION['securimage_code_ctime']);
            }

            $_SESSION['securimage_code_disp'] [$this->namespace] = $this->code_display;
            $_SESSION['securimage_code_value'][$this->namespace] = $this->code;
            $_SESSION['securimage_code_ctime'][$this->namespace] = time();
        }

        $this->saveCodeToDatabase();
    }

    /**
     * Saves the code to the sqlite database
     */
    protected function saveCodeToDatabase()
    {
        $success = false;

        $this->openDatabase();

        if ($this->use_sqlite_db && $this->sqlite_handle !== false) {
            $id      = $this->getCaptchaId(false);
            $ip      = $_SERVER['REMOTE_ADDR'];

            if (empty($id)) {
                $id = $ip;
            }

            $time      = time();
            $code      = $this->code;
            $code_disp = $this->code_display;

            $query = "INSERT OR REPLACE INTO codes(id, ip, code, code_display,"
                    ."namespace, created) VALUES('$id', '$ip', '$code', "
                    ."'$code_disp', '{$this->namespace}', $time)";

            $success = sqlite_query($this->sqlite_handle, $query);
        }

        return $success !== false;
    }

    /**
     * Open sqlite database
     */
    protected function openDatabase()
    {
        $this->sqlite_handle = false;

        if ($this->use_sqlite_db == true && !function_exists('sqlite_open')) {
            trigger_error('Securimage use_sqlite_db option is enable, but SQLIte is not supported by this PHP installation', E_USER_WARNING);
        }

        if ($this->use_sqlite_db && function_exists('sqlite_open')) {
            if (!file_exists($this->sqlite_database)) {
                $fp = fopen($this->sqlite_database, 'w+');
                if (!$fp) {
                    trigger_error('Securimage failed to open sqlite database "' . $this->sqlite_database, E_USER_WARNING);
                    return false;
                }
                fclose($fp);
                chmod($this->sqlite_database, 0666);
            }

            $this->sqlite_handle = sqlite_open($this->sqlite_database, 0666, $error);

            if ($this->sqlite_handle !== false) {
                $res = sqlite_query($this->sqlite_handle, "PRAGMA table_info(codes)");

                if (sqlite_num_rows($res) == 0) {
                    $res = sqlite_query(
                            $this->sqlite_handle,
                            "CREATE TABLE codes (id VARCHAR(40) PRIMARY KEY, ip VARCHAR(32),
                             code VARCHAR(32) NOT NULL, code_display VARCHAR(32) NOT NULL,
                             namespace VARCHAR(32) NOT NULL, created INTEGER)"
                    );
                }

                if (mt_rand(0, 100) / 100.0 == 1.0) {
                    // randomly purge old codes
                    $this->purgeOldCodesFromDatabase();
                }
            }

            return $this->sqlite_handle != false;
        }

        return $this->sqlite_handle;
    }

    /**
     * Get a code from the sqlite database for ip address/captchaId.
     *
     * @return string|array Empty string if no code was found or has expired,
     * otherwise returns the stored captcha code.  If a captchaId is set, this
     * returns an array with indices "code" and "code_disp"
     */
    protected function getCodeFromDatabase()
    {
        $code = '';

        if ($this->use_sqlite_db && $this->sqlite_handle !== false) {
            if (Securimage::$_captchaId !== null) {
                $query = "SELECT * FROM codes WHERE id = '" . sqlite_escape_string(Securimage::$_captchaId) . "'";
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
                $ns = sqlite_escape_string($this->namespace);
                $query = "SELECT * FROM codes WHERE ip = '$ip' AND namespace = '$ns'";
            }

            $res = sqlite_query($this->sqlite_handle, $query);
            if ($res && sqlite_num_rows($res) > 0) {
                $res = sqlite_fetch_array($res);

                if ($this->isCodeExpired($res['created']) == false) {
                    if (Securimage::$_captchaId !== null) {
                        // return an array when using captchaId
                        $code = array('code'      => $res['code'],
                                      'code_disp' => $res['code_display']);
                    } else {
                        // return only the code if no captchaId specified
                        $code = $res['code'];
                    }
                }
            }
        }
        return $code;
    }

    /**
     * Remove an entered code from the database
     */
    protected function clearCodeFromDatabase()
    {
        if (is_resource($this->sqlite_handle)) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $ns = sqlite_escape_string($this->namespace);

            sqlite_query($this->sqlite_handle, "DELETE FROM codes WHERE ip = '$ip' AND namespace = '$ns'");
        }
    }

    /**
     * Deletes old codes from sqlite database
     */
    protected function purgeOldCodesFromDatabase()
    {
        if ($this->use_sqlite_db && $this->sqlite_handle !== false) {
            $now   = time();
            $limit = (!is_numeric($this->expiry_time) || $this->expiry_time < 1) ? 86400 : $this->expiry_time;

            sqlite_query($this->sqlite_handle, "DELETE FROM codes WHERE $now - created > $limit");
        }
    }

    /**
     * Checks to see if the captcha code has expired and cannot be used
     * @param unknown_type $creation_time
     */
    protected function isCodeExpired($creation_time)
    {
        $expired = true;

        if (!is_numeric($this->expiry_time) || $this->expiry_time < 1) {
            $expired = false;
        } else if (time() - $creation_time < $this->expiry_time) {
            $expired = false;
        }

        return $expired;
    }

    /**
     * Generate a wav file given the $letters in the code
     * @todo Add ability to merge 2 sound files together to have random background sounds
     * @param array $letters
     * @return string The binary contents of the wav file
     */
    protected function generateWAV($letters)
    {
        $wavCaptcha = new WavFile();
        $first      = true;     // reading first wav file

        foreach ($letters as $letter) {
            $letter = strtoupper($letter);

            try {
                $l = new WavFile($this->audio_path . '/' . $letter . '.wav');

                if ($first) {
                    // set sample rate, bits/sample, and # of channels for file based on first letter
                    $wavCaptcha->setSampleRate($l->getSampleRate())
                               ->setBitsPerSample($l->getBitsPerSample())
                               ->setNumChannels($l->getNumChannels());
                    $first = false;
                }

                // append letter to the captcha audio
                $wavCaptcha->appendWav($l);

                // random length of silence between $audio_gap_min and $audio_gap_max
                if ($this->audio_gap_max > 0 && $this->audio_gap_max > $this->audio_gap_min) {
                    $wavCaptcha->insertSilence( mt_rand($this->audio_gap_min, $this->audio_gap_max) / 1000.0 );
                }
            } catch (Exception $ex) {
                // failed to open file, or the wav file is broken or not supported
                // 2 wav files were not compatible, different # channels, bits/sample, or sample rate
                throw $ex;
            }
        }

        /********* Set up audio filters *****************************/
        $filters = array();

        if ($this->audio_use_noise == true) {
            // use background audio - find random file
            $noiseFile = $this->getRandomNoiseFile();

            if ($noiseFile !== false && is_readable($noiseFile)) {
                try {
                    $wavNoise = new WavFile($noiseFile, false);
                } catch(Exception $ex) {
                    throw $ex;
                }

                // start at a random offset from the beginning of the wavfile
                // in order to add more randomness
                $randOffset = 0;
                if ($wavNoise->getNumBlocks() > 2 * $wavCaptcha->getNumBlocks()) {
                    $randBlock = rand(0, $wavNoise->getNumBlocks() - $wavCaptcha->getNumBlocks());
                    $wavNoise->readWavData($randBlock * $wavNoise->getBlockAlign(), $wavCaptcha->getNumBlocks() * $wavNoise->getBlockAlign());
                } else {
                    $wavNoise->readWavData();
                    $randOffset = rand(0, $wavNoise->getNumBlocks() - 1);
                }


                $mixOpts = array('wav'  => $wavNoise,
                                 'loop' => true,
                                 'blockOffset' => $randOffset);

                $filters[WavFile::FILTER_MIX]       = $mixOpts;
                $filters[WavFile::FILTER_NORMALIZE] = $this->audio_mix_normalization;
            }
        }

        if ($this->degrade_audio == true) {
            // add random noise.
            // any noise level below 95% is intensely distorted and not pleasant to the ear
            $filters[WavFile::FILTER_DEGRADE] = rand(95, 98) / 100.0;
        }

        if (!empty($filters)) {
            $wavCaptcha->filter($filters);  // apply filters to captcha audio
        }

        return $wavCaptcha->__toString();
    }

    public function getRandomNoiseFile()
    {
        $return = false;

        if ( ($dh = opendir($this->audio_noise_path)) !== false ) {
            $list = array();

            while ( ($file = readdir($dh)) !== false ) {
                if ($file == '.' || $file == '..') continue;
                if (strtolower(substr($file, -4)) != '.wav') continue;

                $list[] = $file;
            }

            closedir($dh);

            if (sizeof($list) > 0) {
                $file   = $list[array_rand($list, 1)];
                $return = $this->audio_noise_path . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $return;
    }

    /**
     * Return a wav file saying there was an error generating file
     *
     * @return string The binary audio contents
     */
    protected function audioError()
    {
        return @file_get_contents(dirname(__FILE__) . '/audio/error.wav');
    }

    /**
     * Checks to see if headers can be sent and if any error has been output to the browser
     *
     * @return bool true if headers haven't been sent and no output/errors will break audio/images, false if unsafe
     */
    protected function canSendHeaders()
    {
        if (headers_sent()) {
            // output has been flushed and headers have already been sent
            return false;
        } else if (function_exists('error_get_last') && null !== error_get_last() && ini_get('display_errors') != false) {
            // an error has been output and display_errors is on
            return false;
        } else if (strlen((string)ob_get_contents()) > 0) {
            // headers haven't been sent, but there is data in the buffer that will break image and audio data
            return false;
        }

        return true;
    }

    /**
     * Return a random float between 0 and 0.9999
     *
     * @return float Random float between 0 and 0.9999
     */
    function frand()
    {
        return 0.0001 * rand(0,9999);
    }

    /**
     * Convert an html color code to a Securimage_Color
     * @param string $color
     * @param Securimage_Color $default The defalt color to use if $color is invalid
     */
    protected function initColor($color, $default)
    {
        if ($color == null) {
            return new Securimage_Color($default);
        } else if (is_string($color)) {
            try {
                return new Securimage_Color($color);
            } catch(Exception $e) {
                return new Securimage_Color($default);
            }
        } else if (is_array($color) && sizeof($color) == 3) {
            return new Securimage_Color($color[0], $color[1], $color[2]);
        } else {
            return new Securimage_Color($default);
        }
    }

    /**
     * Error handler used when outputting captcha image or audio.
     * This error handler helps determine if any errors raised would
     * prevent captcha image or audio from displaying.  If they have
     * no effect on the output buffer or headers, true is returned so
     * the script can continue processing.
     * See https://github.com/dapphp/securimage/issues/15
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return boolean true if handled, false if PHP should handle
     */
    public function errorHandler($errno, $errstr, $errfile = '', $errline = 0, $errcontext = array())
    {
        // get the current error reporting level
        $level = error_reporting();

        // if error was supressed or $errno not set in current error level
        if ($level == 0 || ($level & $errno) == 0) {
            return true;
        }

        return false;
    }
}


/**
 * Color object for Securimage CAPTCHA
 *
 * @version 3.0
 * @since 2.0
 * @package Securimage
 * @subpackage classes
 *
 */
class Securimage_Color
{
    public $r;
    public $g;
    public $b;

    /**
     * Create a new Securimage_Color object.<br />
     * Constructor expects 1 or 3 arguments.<br />
     * When passing a single argument, specify the color using HTML hex format,<br />
     * when passing 3 arguments, specify each RGB component (from 0-255) individually.<br />
     * $color = new Securimage_Color('#0080FF') or <br />
     * $color = new Securimage_Color(0, 128, 255)
     *
     * @param string $color
     * @throws Exception
     */
    public function __construct($color = '#ffffff')
    {
        $args = func_get_args();

        if (sizeof($args) == 0) {
            $this->r = 255;
            $this->g = 255;
            $this->b = 255;
        } else if (sizeof($args) == 1) {
            // set based on html code
            if (substr($color, 0, 1) == '#') {
                $color = substr($color, 1);
            }

            if (strlen($color) != 3 && strlen($color) != 6) {
                throw new InvalidArgumentException(
                  'Invalid HTML color code passed to Securimage_Color'
                );
            }

            $this->constructHTML($color);
        } else if (sizeof($args) == 3) {
            $this->constructRGB($args[0], $args[1], $args[2]);
        } else {
            throw new InvalidArgumentException(
              'Securimage_Color constructor expects 0, 1 or 3 arguments; ' . sizeof($args) . ' given'
            );
        }
    }

    /**
     * Construct from an rgb triplet
     * @param int $red The red component, 0-255
     * @param int $green The green component, 0-255
     * @param int $blue The blue component, 0-255
     */
    protected function constructRGB($red, $green, $blue)
    {
        if ($red < 0)     $red   = 0;
        if ($red > 255)   $red   = 255;
        if ($green < 0)   $green = 0;
        if ($green > 255) $green = 255;
        if ($blue < 0)    $blue  = 0;
        if ($blue > 255)  $blue  = 255;

        $this->r = $red;
        $this->g = $green;
        $this->b = $blue;
    }

    /**
     * Construct from an html hex color code
     * @param string $color
     */
    protected function constructHTML($color)
    {
        if (strlen($color) == 3) {
            $red   = str_repeat(substr($color, 0, 1), 2);
            $green = str_repeat(substr($color, 1, 1), 2);
            $blue  = str_repeat(substr($color, 2, 1), 2);
        } else {
            $red   = substr($color, 0, 2);
            $green = substr($color, 2, 2);
            $blue  = substr($color, 4, 2);
        }

        $this->r = hexdec($red);
        $this->g = hexdec($green);
        $this->b = hexdec($blue);
    }
}



/**
* Project: PHPWavUtils: Classes for creating, reading, and manipulating WAV files in PHP<br />
* File: WavFile.php<br />
*
* Copyright (c) 2012, Drew Phillips
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without modification,
* are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
* this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright notice,
* this list of conditions and the following disclaimer in the documentation
* and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* Any modifications to the library should be indicated clearly in the source code
* to inform users that the changes are not a part of the original software.<br /><br />
*
* @copyright 2012 Drew Phillips
* @author Drew Phillips <drew@drew-phillips.com>
* @author Paul Voegler <http://www.voegler.eu/>
* @version 1.0RC1 (April 2012)
* @package PHPWavUtils
* @license BSD License
*
* Changelog:
*
*   1.0 RC1 (4/20/2012)
*     - Initial release candidate
*     - Supports 8, 16, 24, 32 bit PCM, 32-bit IEEE FLOAT, Extensible Format
*     - Support for 18 channels of audio
*     - Ability to read an offset from a file to reduce memory footprint with large files
*     - Single-pass audio filter processing
*     - Highly accurate and efficient mix and normalization filters (http://www.voegler.eu/pub/audio/)
*     - Utility filters for degrading audio, and inserting silence
*
*   0.6 (4/12/2012)
*     - Support 8, 16, 24, 32 bit and PCM float (Paul Voegler)
*     - Add normalize filter, misc improvements and fixes (Paul Voegler)
*     - Normalize parameters to filter() to use filter constants as array indices
*     - Add option to mix filter to loop the target file if the source is longer
*
*   0.5 (4/3/2012)
*     - Fix binary pack routine (Paul Voegler)
*     - Add improved mixing function (Paul Voegler)
*
*/

class WavFile
{
    /*%******************************************************************************************%*/
    // Class constants

    /** @var int Filter flag for mixing two files */
    const FILTER_MIX       = 0x01;

    /** @var int Filter flag for normalizing audio data */
    const FILTER_NORMALIZE = 0x02;

    /** @var int Filter flag for degrading audio data */
    const FILTER_DEGRADE   = 0x04;

    /** @var int Maximum number of channels */
    const MAX_CHANNEL = 18;

    /** @var int Maximum sample rate */
    const MAX_SAMPLERATE = 192000;

    /** Channel Locations for ChannelMask */
    const SPEAKER_DEFAULT               = 0x000000;
    const SPEAKER_FRONT_LEFT            = 0x000001;
    const SPEAKER_FRONT_RIGHT           = 0x000002;
    const SPEAKER_FRONT_CENTER          = 0x000004;
    const SPEAKER_LOW_FREQUENCY         = 0x000008;
    const SPEAKER_BACK_LEFT             = 0x000010;
    const SPEAKER_BACK_RIGHT            = 0x000020;
    const SPEAKER_FRONT_LEFT_OF_CENTER  = 0x000040;
    const SPEAKER_FRONT_RIGHT_OF_CENTER = 0x000080;
    const SPEAKER_BACK_CENTER           = 0x000100;
    const SPEAKER_SIDE_LEFT             = 0x000200;
    const SPEAKER_SIDE_RIGHT            = 0x000400;
    const SPEAKER_TOP_CENTER            = 0x000800;
    const SPEAKER_TOP_FRONT_LEFT        = 0x001000;
    const SPEAKER_TOP_FRONT_CENTER      = 0x002000;
    const SPEAKER_TOP_FRONT_RIGHT       = 0x004000;
    const SPEAKER_TOP_BACK_LEFT         = 0x008000;
    const SPEAKER_TOP_BACK_CENTER       = 0x010000;
    const SPEAKER_TOP_BACK_RIGHT        = 0x020000;
    const SPEAKER_ALL                   = 0x03FFFF;

    /** @var int PCM Audio Format */
    const WAVE_FORMAT_PCM           = 0x0001;

    /** @var int IEEE FLOAT Audio Format */
    const WAVE_FORMAT_IEEE_FLOAT    = 0x0003;

    /** @var int EXTENSIBLE Audio Format - actual audio format defined by SubFormat */
    const WAVE_FORMAT_EXTENSIBLE    = 0xFFFE;

    /** @var string PCM Audio Format SubType - LE hex representation of GUID {00000001-0000-0010-8000-00AA00389B71} */
    const WAVE_SUBFORMAT_PCM        = "0100000000001000800000aa00389b71";

    /** @var string IEEE FLOAT Audio Format SubType - LE hex representation of GUID {00000003-0000-0010-8000-00AA00389B71} */
    const WAVE_SUBFORMAT_IEEE_FLOAT = "0300000000001000800000aa00389b71";


    /*%******************************************************************************************%*/
    // Properties

    /** @var array Log base modifier lookup table for a given threshold (in 0.05 steps) used by normalizeSample.
     * Adjusts the slope (1st derivative) of the log function at the threshold to 1 for a smooth transition
     * from linear to logarithmic amplitude output. */
    protected static $LOOKUP_LOGBASE = array(
        2.513, 2.667, 2.841, 3.038, 3.262,
        3.520, 3.819, 4.171, 4.589, 5.093,
        5.711, 6.487, 7.483, 8.806, 10.634,
        13.302, 17.510, 24.970, 41.155, 96.088
    );

    /** @var int The actual physical file size */
    protected $_actualSize;

    /** @var int The size of the file in RIFF header */
    protected $_chunkSize;

    /** @var int The size of the "fmt " chunk */
    protected $_fmtChunkSize;

    /** @var int The size of the extended "fmt " data */
    protected $_fmtExtendedSize;

    /** @var int The size of the "fact" chunk */
    protected $_factChunkSize;

    /** @var int Size of the data chunk */
    protected $_dataSize;

    /** @var int Size of the data chunk in the opened wav file */
    protected $_dataSize_fp;

    /** @var int Does _dataSize really reflect strlen($_samples)? Case when a wav file is read with readData = false */
    protected $_dataSize_valid;

    /** @var int Starting offset of data chunk */
    protected $_dataOffset;

    /** @var int The audio format - WavFile::WAVE_FORMAT_* */
    protected $_audioFormat;

    /** @var int The audio subformat - WavFile::WAVE_SUBFORMAT_* */
    protected $_audioSubFormat;

    /** @var int Number of channels in the audio file */
    protected $_numChannels;

    /** @var int The channel mask */
    protected $_channelMask;

    /** @var int Samples per second */
    protected $_sampleRate;

    /** @var int Number of bits per sample */
    protected $_bitsPerSample;

    /** @var int Number of valid bits per sample */
    protected $_validBitsPerSample;

    /** @var int NumChannels * BitsPerSample/8 */
    protected $_blockAlign;

    /** @var int Number of sample blocks */
    protected $_numBlocks;

    /** @var int Bytes per second */
    protected $_byteRate;

    /** @var string Binary string of samples */
    protected $_samples;

    /** @var resource The file pointer used for reading wavs from file or memory */
    protected $_fp;


    /*%******************************************************************************************%*/
    // Special methods

    /**
     * WavFile Constructor.
     *
     * <code>
     * $wav1 = new WavFile(2, 44100, 16);         // new wav with 2 channels, at 44100 samples/sec and 16 bits per sample
     * $wav2 = new WavFile('./audio/sound.wav');  // open and read wav file
     * </code>
     *
     * @param string|int $numChannelsOrFileName  (Optional) If string, the filename of the wav file to open. The number of channels otherwise. Defaults to 1.
     * @param int|bool $sampleRateOrReadData  (Optional) If opening a file and boolean, decides whether to read the data chunk or not. Defaults to true. The sample rate in samples per second otherwise. 8000 = standard telephone, 16000 = wideband telephone, 32000 = FM radio and 44100 = CD quality. Defaults to 8000.
     * @param int $bitsPerSample  (Optional) The number of bits per sample. Has to be 8, 16 or 24 for PCM audio or 32 for IEEE FLOAT audio. 8 = telephone, 16 = CD and 24 or 32 = studio quality. Defaults to 8.
     * @throws WavFormatException
     * @throws WavFileException
     */
    public function __construct($numChannelsOrFileName = null, $sampleRateOrReadData = null, $bitsPerSample = null)
    {
        $this->_actualSize         = 44;
        $this->_chunkSize          = 36;
        $this->_fmtChunkSize       = 16;
        $this->_fmtExtendedSize    = 0;
        $this->_factChunkSize      = 0;
        $this->_dataSize           = 0;
        $this->_dataSize_fp        = 0;
        $this->_dataSize_valid     = true;
        $this->_dataOffset         = 44;
        $this->_audioFormat        = self::WAVE_FORMAT_PCM;
        $this->_audioSubFormat     = null;
        $this->_numChannels        = 1;
        $this->_channelMask        = self::SPEAKER_DEFAULT;
        $this->_sampleRate         = 8000;
        $this->_bitsPerSample      = 8;
        $this->_validBitsPerSample = 8;
        $this->_blockAlign         = 1;
        $this->_numBlocks          = 0;
        $this->_byteRate           = 8000;
        $this->_samples            = '';
        $this->_fp                 = null;


        if (is_string($numChannelsOrFileName)) {
            $this->openWav($numChannelsOrFileName, is_bool($sampleRateOrReadData) ? $sampleRateOrReadData : true);

        } else {
            $this->setNumChannels(is_null($numChannelsOrFileName) ? 1 : $numChannelsOrFileName)
                 ->setSampleRate(is_null($sampleRateOrReadData) ? 8000 : $sampleRateOrReadData)
                 ->setBitsPerSample(is_null($bitsPerSample) ? 8 : $bitsPerSample);
        }
    }

    public function __destruct() {
        if (is_resource($this->_fp)) $this->closeWav();
    }

    public function __clone() {
        $this->_fp = null;
    }

    /**
     * Output the wav file headers and data.
     *
     * @return string  The encoded file.
     */
    public function __toString()
    {
        return $this->makeHeader() .
               $this->getDataSubchunk();
    }


    /*%******************************************************************************************%*/
    // Static methods

    /**
     * Unpacks a single binary sample to numeric value.
     *
     * @param string $sampleBinary  (Required) The sample to decode.
     * @param int $bitDepth  (Optional) The bits per sample to decode. If omitted, derives it from the length of $sampleBinary.
     * @return int|float  The numeric sample value. Float for 32-bit samples. Returns null for unsupported bit depths.
     */
    public static function unpackSample($sampleBinary, $bitDepth = null)
    {
        if ($bitDepth === null) {
            $bitDepth = strlen($sampleBinary) * 8;
        }

        switch ($bitDepth) {
            case 8:
                // unsigned char
                return ord($sampleBinary);

            case 16:
                // signed short, little endian
                $data = unpack('v', $sampleBinary);
                $sample = $data[1];
                if ($sample >= 0x8000) {
                    $sample -= 0x10000;
                }
                return $sample;

            case 24:
                // 3 byte packed signed integer, little endian
                $data = unpack('C3', $sampleBinary);
                $sample = $data[1] | ($data[2] << 8) | ($data[3] << 16);
                if ($sample >= 0x800000) {
                    $sample -= 0x1000000;
                }
                return $sample;

            case 32:
                // 32-bit float
                $data = unpack('f', $sampleBinary);
                return $data[1];

            default:
                return null;
        }
    }

    /**
     * Packs a single numeric sample to binary.
     *
     * @param int|float $sample  (Required) The sample to encode. Has to be within valid range for $bitDepth. Float values only for 32 bits.
     * @param int $bitDepth  (Required) The bits per sample to encode with.
     * @return string  The encoded binary sample. Returns null for unsupported bit depths.
     */
    public static function packSample($sample, $bitDepth)
    {
        switch ($bitDepth) {
            case 8:
                // unsigned char
                return chr($sample);

            case 16:
                // signed short, little endian
                if ($sample < 0) {
                    $sample += 0x10000;
                }
                return pack('v', $sample);

            case 24:
                // 3 byte packed signed integer, little endian
                if ($sample < 0) {
                    $sample += 0x1000000;
                }
                return pack('C3', $sample & 0xff, ($sample >>  8) & 0xff, ($sample >> 16) & 0xff);

            case 32:
                // 32-bit float
                return pack('f', $sample);

            default:
                return null;
        }
    }

    /**
     * Unpacks a binary sample block to numeric values.
     *
     * @param string $sampleBlock  (Required) The binary sample block (all channels).
     * @param int $bitDepth  (Required) The bits per sample to decode.
     * @param int $numChannels  (Optional) The number of channels to decode. If omitted, derives it from the length of $sampleBlock and $bitDepth.
     * @return array  The sample values as an array of integers of floats for 32 bits. First channel is array index 1.
     */
    public static function unpackSampleBlock($sampleBlock, $bitDepth, $numChannels = null) {
        $sampleBytes = $bitDepth / 8;
        if ($numChannels === null) {
            $numChannels = strlen($sampleBlock) / $sampleBytes;
        }

        $samples = array();
        for ($i = 0; $i < $numChannels; $i++) {
            $sampleBinary = substr($sampleBlock, $i * $sampleBytes, $sampleBytes);
            $samples[$i + 1] = self::unpackSample($sampleBinary, $bitDepth);
        }

        return $samples;
    }

    /**
     * Packs an array of numeric channel samples to a binary sample block.
     *
     * @param array $samples  (Required) The array of channel sample values. Expects float values for 32 bits and integer otherwise.
     * @param int $bitDepth  (Required) The bits per sample to encode with.
     * @return string  The encoded binary sample block.
     */
    public static function packSampleBlock($samples, $bitDepth) {
        $sampleBlock = '';
        foreach($samples as $sample) {
            $sampleBlock .= self::packSample($sample, $bitDepth);
        }

        return $sampleBlock;
    }

    /**
     * Normalizes a float audio sample. Maximum input range assumed for compression is [-2, 2].
     * See http://www.voegler.eu/pub/audio/ for more information.
     *
     * @param float $sampleFloat  (Required) The float sample to normalize.
     * @param float $threshold  (Required) The threshold or gain factor for normalizing the amplitude. <ul>
     *     <li> >= 1 - Normalize by multiplying by the threshold (boost - positive gain). <br />
     *            A value of 1 in effect means no normalization (and results in clipping). </li>
     *     <li> <= -1 - Normalize by dividing by the the absolute value of threshold (attenuate - negative gain). <br />
     *            A factor of 2 (-2) is about 6dB reduction in volume.</li>
     *     <li> [0, 1) - (open inverval - not including 1) - The threshold
     *            above which amplitudes are comressed logarithmically. <br />
     *            e.g. 0.6 to leave amplitudes up to 60% "as is" and compress above. </li>
     *     <li> (-1, 0) - (open inverval - not including -1 and 0) - The threshold
     *            above which amplitudes are comressed linearly. <br />
     *            e.g. -0.6 to leave amplitudes up to 60% "as is" and compress above. </li></ul>
     * @return float  The normalized sample.
     **/
    public static function normalizeSample($sampleFloat, $threshold) {
        // apply positive gain
        if ($threshold >= 1) {
            return $sampleFloat * $threshold;
        }

        // apply negative gain
        if ($threshold <= -1) {
            return $sampleFloat / -$threshold;
        }

        $sign = $sampleFloat < 0 ? -1 : 1;
        $sampleAbs = abs($sampleFloat);

        // logarithmic compression
        if ($threshold >= 0 && $threshold < 1 && $sampleAbs > $threshold) {
            $loga = self::$LOOKUP_LOGBASE[(int)($threshold * 20)]; // log base modifier
            return $sign * ($threshold + (1 - $threshold) * log(1 + $loga * ($sampleAbs - $threshold) / (2 - $threshold)) / log(1 + $loga));
        }

        // linear compression
        $thresholdAbs = abs($threshold);
        if ($threshold > -1 && $threshold < 0 && $sampleAbs > $thresholdAbs) {
            return $sign * ($thresholdAbs + (1 - $thresholdAbs) / (2 - $thresholdAbs) * ($sampleAbs - $thresholdAbs));
        }

        // else ?
        return $sampleFloat;
    }


    /*%******************************************************************************************%*/
    // Getter and Setter methods for properties

    public function getActualSize() {
        return $this->_actualSize;
    }

    protected function setActualSize($actualSize = null) {
        if (is_null($actualSize)) {
            $this->_actualSize = 8 + $this->_chunkSize;  // + "RIFF" header (ID + size)
        } else {
            $this->_actualSize = $actualSize;
        }

        return $this;
    }

    public function getChunkSize() {
        return $this->_chunkSize;
    }

    protected function setChunkSize($chunkSize = null) {
        if (is_null($chunkSize)) {
            $this->_chunkSize = 4 +                                                            // "WAVE" chunk
                                8 + $this->_fmtChunkSize +                                     // "fmt " subchunk
                                ($this->_factChunkSize > 0 ? 8 + $this->_factChunkSize : 0) +  // "fact" subchunk
                                8 + $this->_dataSize +                                         // "data" subchunk
                                ($this->_dataSize & 1);                                        // padding byte
        } else {
            $this->_chunkSize = $chunkSize;
        }

        $this->setActualSize();

        return $this;
    }

    public function getFmtChunkSize() {
        return $this->_fmtChunkSize;
    }

    protected function setFmtChunkSize($fmtChunkSize = null) {
        if (is_null($fmtChunkSize)) {
            $this->_fmtChunkSize = 16 + $this->_fmtExtendedSize;
        } else {
            $this->_fmtChunkSize = $fmtChunkSize;
        }

        $this->setChunkSize()    // implicit setActualSize()
             ->setDataOffset();

        return $this;
    }

    public function getFmtExtendedSize() {
        return $this->_fmtExtendedSize;
    }

    protected function setFmtExtendedSize($fmtExtendedSize = null) {
        if (is_null($fmtExtendedSize)) {
            if ($this->_audioFormat == self::WAVE_FORMAT_EXTENSIBLE) {
                $this->_fmtExtendedSize = 2 + 22;                          // extension size for WAVE_FORMAT_EXTENSIBLE
            } elseif ($this->_audioFormat != self::WAVE_FORMAT_PCM) {
                $this->_fmtExtendedSize = 2 + 0;                           // empty extension
            } else {
                $this->_fmtExtendedSize = 0;                               // no extension, only for WAVE_FORMAT_PCM
            }
        } else {
            $this->_fmtExtendedSize = $fmtExtendedSize;
        }

        $this->setFmtChunkSize();  // implicit setSize(), setActualSize(), setDataOffset()

        return $this;
    }

    public function getFactChunkSize() {
        return $this->_factChunkSize;
    }

    protected function setFactChunkSize($factChunkSize = null) {
        if (is_null($factChunkSize)) {
            if ($this->_audioFormat != self::WAVE_FORMAT_PCM) {
                $this->_factChunkSize = 4;
            } else {
                $this->_factChunkSize = 0;
            }
        } else {
            $this->_factChunkSize = $factChunkSize;
        }

        $this->setChunkSize()    // implicit setActualSize()
             ->setDataOffset();

        return $this;
    }

    public function getDataSize() {
        return $this->_dataSize;
    }

    protected function setDataSize($dataSize = null) {
        if (is_null($dataSize)) {
            $this->_dataSize = strlen($this->_samples);
        } else {
            $this->_dataSize = $dataSize;
        }

        $this->setChunkSize()   // implicit setActualSize()
             ->setNumBlocks();
        $this->_dataSize_valid = true;

        return $this;
    }

    public function getDataOffset() {
        return $this->_dataOffset;
    }

    protected function setDataOffset($dataOffset = null) {
        if (is_null($dataOffset)) {
            $this->_dataOffset = 8 +                                                            // "RIFF" header (ID + size)
                                 4 +                                                            // "WAVE" chunk
                                 8 + $this->_fmtChunkSize +                                     // "fmt " subchunk
                                 ($this->_factChunkSize > 0 ? 8 + $this->_factChunkSize : 0) +  // "fact" subchunk
                                 8;                                                             // "data" subchunk
        } else {
            $this->_dataOffset = $dataOffset;
        }

        return $this;
    }

    public function getAudioFormat() {
        return $this->_audioFormat;
    }

    protected function setAudioFormat($audioFormat = null) {
        if (is_null($audioFormat)) {
            if (($this->_bitsPerSample <= 16 || $this->_bitsPerSample == 32)
              && $this->_validBitsPerSample == $this->_bitsPerSample
              && $this->_channelMask == self::SPEAKER_DEFAULT
              && $this->_numChannels <= 2) {
                if ($this->_bitsPerSample <= 16) {
                    $this->_audioFormat = self::WAVE_FORMAT_PCM;
                } else {
                    $this->_audioFormat = self::WAVE_FORMAT_IEEE_FLOAT;
                }
            } else {
                $this->_audioFormat = self::WAVE_FORMAT_EXTENSIBLE;
            }
        } else {
            $this->_audioFormat = $audioFormat;
        }

        $this->setAudioSubFormat()
             ->setFactChunkSize()     // implicit setSize(), setActualSize(), setDataOffset()
             ->setFmtExtendedSize();  // implicit setFmtChunkSize(), setSize(), setActualSize(), setDataOffset()

        return $this;
    }

    public function getAudioSubFormat() {
        return $this->_audioSubFormat;
    }

    protected function setAudioSubFormat($audioSubFormat = null) {
        if (is_null($audioSubFormat)) {
            if ($this->_bitsPerSample == 32) {
                $this->_audioSubFormat = self::WAVE_SUBFORMAT_IEEE_FLOAT;  // 32 bits are IEEE FLOAT in this class
            } else {
                $this->_audioSubFormat = self::WAVE_SUBFORMAT_PCM;         // 8, 16 and 24 bits are PCM in this class
            }
        } else {
            $this->_audioSubFormat = $audioSubFormat;
        }

        return $this;
    }

    public function getNumChannels() {
        return $this->_numChannels;
    }

    public function setNumChannels($numChannels) {
        if ($numChannels < 1 || $numChannels > self::MAX_CHANNEL) {
            throw new WavFileException('Unsupported number of channels. Only up to ' . self::MAX_CHANNEL . ' channels are supported.');
        } elseif ($this->_samples !== '') {
            trigger_error('Wav already has sample data. Changing the number of channels does not convert and may corrupt the data.', E_USER_NOTICE);
        }

        $this->_numChannels = (int)$numChannels;

        $this->setAudioFormat()  // implicit setAudioSubFormat(), setFactChunkSize(), setFmtExtendedSize(), setFmtChunkSize(), setSize(), setActualSize(), setDataOffset()
             ->setByteRate()
             ->setBlockAlign();  // implicit setNumBlocks()

        return $this;
    }

    public function getChannelMask() {
        return $this->_channelMask;
    }

    public function setChannelMask($channelMask = self::SPEAKER_DEFAULT) {
        if ($channelMask != 0) {
            // count number of set bits - Hamming weight
            $c = (int)$channelMask;
            $n = 0;
            while ($c > 0) {
                $n += $c & 1;
                $c >>= 1;
            }
            if ($n != $this->_numChannels || (((int)$channelMask | self::SPEAKER_ALL) != self::SPEAKER_ALL)) {
                throw new WavFileException('Invalid channel mask. The number of channels does not match the number of locations in the mask.');
            }
        }

        $this->_channelMask = (int)$channelMask;

        $this->setAudioFormat();  // implicit setAudioSubFormat(), setFactChunkSize(), setFmtExtendedSize(), setFmtChunkSize(), setSize(), setActualSize(), setDataOffset()

        return $this;
    }

    public function getSampleRate() {
        return $this->_sampleRate;
    }

    public function setSampleRate($sampleRate) {
        if ($sampleRate < 1 || $sampleRate > self::MAX_SAMPLERATE) {
            throw new WavFileException('Invalid sample rate.');
        } elseif ($this->_samples !== '') {
            trigger_error('Wav already has sample data. Changing the sample rate does not convert the data and may yield undesired results.', E_USER_NOTICE);
        }

        $this->_sampleRate = (int)$sampleRate;

        $this->setByteRate();

        return $this;
    }

    public function getBitsPerSample() {
        return $this->_bitsPerSample;
    }

    public function setBitsPerSample($bitsPerSample) {
        if (!in_array($bitsPerSample, array(8, 16, 24, 32))) {
            throw new WavFileException('Unsupported bits per sample. Only 8, 16, 24 and 32 bits are supported.');
        } elseif ($this->_samples !== '') {
            trigger_error('Wav already has sample data. Changing the bits per sample does not convert and may corrupt the data.', E_USER_NOTICE);
        }

        $this->_bitsPerSample = (int)$bitsPerSample;

        $this->setValidBitsPerSample()  // implicit setAudioFormat(), setAudioSubFormat(), setFmtChunkSize(), setFactChunkSize(), setSize(), setActualSize(), setDataOffset()
             ->setByteRate()
             ->setBlockAlign();         // implicit setNumBlocks()

        return $this;
    }

    public function getValidBitsPerSample() {
        return $this->_validBitsPerSample;
    }

    protected function setValidBitsPerSample($validBitsPerSample = null) {
        if (is_null($validBitsPerSample)) {
            $this->_validBitsPerSample = $this->_bitsPerSample;
        } else {
            if ($validBitsPerSample < 1 || $validBitsPerSample > $this->_bitsPerSample) {
                throw new WavFileException('ValidBitsPerSample cannot be greater than BitsPerSample.');
            }
            $this->_validBitsPerSample = (int)$validBitsPerSample;
        }

        $this->setAudioFormat();  // implicit setAudioSubFormat(), setFactChunkSize(), setFmtExtendedSize(), setFmtChunkSize(), setSize(), setActualSize(), setDataOffset()

        return $this;
    }

    public function getBlockAlign() {
        return $this->_blockAlign;
    }

    protected function setBlockAlign($blockAlign = null) {
        if (is_null($blockAlign)) {
            $this->_blockAlign = $this->_numChannels * $this->_bitsPerSample / 8;
        } else {
            $this->_blockAlign = $blockAlign;
        }

        $this->setNumBlocks();

        return $this;
    }

    public function getNumBlocks()
    {
        return $this->_numBlocks;
    }

    protected function setNumBlocks($numBlocks = null) {
        if (is_null($numBlocks)) {
            $this->_numBlocks = (int)($this->_dataSize / $this->_blockAlign);  // do not count incomplete sample blocks
        } else {
            $this->_numBlocks = $numBlocks;
        }

        return $this;
    }

    public function getByteRate() {
        return $this->_byteRate;
    }

    protected function setByteRate($byteRate = null) {
        if (is_null($byteRate)) {
            $this->_byteRate = $this->_sampleRate * $this->_numChannels * $this->_bitsPerSample / 8;
        } else {
            $this->_byteRate = $byteRate;
        }

        return $this;
    }

    public function getSamples() {
        return $this->_samples;
    }

    public function setSamples(&$samples = '') {
        if (strlen($samples) % $this->_blockAlign != 0) {
            throw new WavFileException('Incorrect samples size. Has to be a multiple of BlockAlign.');
        }

        $this->_samples = $samples;

        $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()

        return $this;
    }


    /*%******************************************************************************************%*/
    // Getters

    public function getMinAmplitude()
    {
        if ($this->_bitsPerSample == 8) {
            return 0;
        } elseif ($this->_bitsPerSample == 32) {
            return -1.0;
        } else {
            return -(1 << ($this->_bitsPerSample - 1));
        }
    }

    public function getZeroAmplitude()
    {
        if ($this->_bitsPerSample == 8) {
            return 0x80;
        } elseif ($this->_bitsPerSample == 32) {
            return 0.0;
        } else {
            return 0;
        }
    }

    public function getMaxAmplitude()
    {
        if($this->_bitsPerSample == 8) {
            return 0xFF;
        } elseif($this->_bitsPerSample == 32) {
            return 1.0;
        } else {
            return (1 << ($this->_bitsPerSample - 1)) - 1;
        }
    }


    /*%******************************************************************************************%*/
    // Wave file methods

    /**
     * Construct a wav header from this object. Includes "fact" chunk in necessary.
     * http://www-mmsp.ece.mcgill.ca/documents/audioformats/wave/wave.html
     *
     * @return string  The RIFF header data.
     */
    public function makeHeader()
    {
        // reset and recalculate
        $this->setAudioFormat();                                    // implicit setAudioSubFormat(), setFactChunkSize(), setFmtExtendedSize(), setFmtChunkSize(), setSize(), setActualSize(), setDataOffset()
        $this->setNumBlocks();

        // RIFF header
        $header = pack('N', 0x52494646);                            // ChunkID - "RIFF"
        $header .= pack('V', $this->getChunkSize());                // ChunkSize
        $header .= pack('N', 0x57415645);                           // Format - "WAVE"

        // "fmt " subchunk
        $header .= pack('N', 0x666d7420);                           // SubchunkID - "fmt "
        $header .= pack('V', $this->getFmtChunkSize());             // SubchunkSize
        $header .= pack('v', $this->getAudioFormat());              // AudioFormat
        $header .= pack('v', $this->getNumChannels());              // NumChannels
        $header .= pack('V', $this->getSampleRate());               // SampleRate
        $header .= pack('V', $this->getByteRate());                 // ByteRate
        $header .= pack('v', $this->getBlockAlign());               // BlockAlign
        $header .= pack('v', $this->getBitsPerSample());            // BitsPerSample
        if($this->getFmtExtendedSize() == 24) {
            $header .= pack('v', 22);                               // extension size = 24 bytes, cbSize: 24 - 2 = 22 bytes
            $header .= pack('v', $this->getValidBitsPerSample());   // ValidBitsPerSample
            $header .= pack('V', $this->getChannelMask());          // ChannelMask
            $header .= pack('H32', $this->getAudioSubFormat());     // SubFormat
        } elseif ($this->getFmtExtendedSize() == 2) {
            $header .= pack('v', 0);                                // extension size = 2 bytes, cbSize: 2 - 2 = 0 bytes
        }

        // "fact" subchunk
        if ($this->getFactChunkSize() == 4) {
            $header .= pack('N', 0x66616374);                       // SubchunkID - "fact"
            $header .= pack('V', 4);                                // SubchunkSize
            $header .= pack('V', $this->getNumBlocks());            // SampleLength (per channel)
        }

        return $header;
    }

    /**
     * Construct wav DATA chunk.
     *
     * @return string  The DATA header and chunk.
     */
    public function getDataSubchunk()
    {
        // check preconditions
        if (!$this->_dataSize_valid) {
            $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()
        }


        // create subchunk
        return pack('N', 0x64617461) .                    // SubchunkID - "data"
               pack('V', $this->getDataSize()) .          // SubchunkSize
               $this->_samples .                          // Subchunk data
               ($this->getDataSize() & 1 ? chr(0) : '');  // padding byte
    }

    /**
     * Save the wav data to a file.
     *
     * @param string $filename  (Required) The file path to save the wav to.
     * @throws WavFileException
     */
    public function save($filename)
    {
        $fp = @fopen($filename, 'w+b');
        if (!is_resource($fp)) {
            throw new WavFileException('Failed to open "' . $filename . '" for writing.');
        }

        fwrite($fp, $this->makeHeader());
        fwrite($fp, $this->getDataSubchunk());
        fclose($fp);

        return $this;
    }

    /**
     * Reads a wav header and data from a file.
     *
     * @param string $filename  (Required) The path to the wav file to read.
     * @param bool $readData  (Optional) If true, also read the data chunk.
     * @throws WavFormatException
     * @throws WavFileException
     */
    public function openWav($filename, $readData = true)
    {
        // check preconditions
        if (!file_exists($filename)) {
            throw new WavFileException('Failed to open "' . $filename . '". File not found.');
        } elseif (!is_readable($filename)) {
            throw new WavFileException('Failed to open "' . $filename . '". File is not readable.');
        } elseif (is_resource($this->_fp)) {
            $this->closeWav();
        }


        // open the file
        $this->_fp = @fopen($filename, 'rb');
        if (!is_resource($this->_fp)) {
            throw new WavFileException('Failed to open "' . $filename . '".');
        }

        // read the file
        return $this->readWav($readData);
    }

    /**
     * Close a with openWav() previously opened wav file or free the buffer of setWavData().
     * Not necessary if the data has been read (readData = true) already.
     */
    public function closeWav() {
        if (is_resource($this->_fp)) fclose($this->_fp);

        return $this;
    }

    /**
     * Set the wav file data and properties from a wav file in a string.
     *
     * @param string $data  (Required) The wav file data. Passed by reference.
     * @param bool $free  (Optional) True to free the passed $data after copying.
     * @throws WavFormatException
     * @throws WavFileException
     */
    public function setWavData(&$data, $free = true)
    {
        // check preconditions
        if (is_resource($this->_fp)) $this->closeWav();


        // open temporary stream in memory
        $this->_fp = @fopen('php://memory', 'w+b');
        if (!is_resource($this->_fp)) {
            throw new WavFileException('Failed to open memory stream to write wav data. Use openWav() instead.');
        }

        // prepare stream
        fwrite($this->_fp, $data);
        rewind($this->_fp);

        // free the passed data
        if ($free) $data = null;

        // read the stream like a file
        return $this->readWav(true);
    }

    /**
     * Read wav file from a stream.
     *
     * @param $readData  (Optional) If true, also read the data chunk.
     * @throws WavFormatException
     * @throws WavFileException
     */
    protected function readWav($readData = true)
    {
        if (!is_resource($this->_fp)) {
            throw new WavFileException('No wav file open. Use openWav() first.');
        }

        try {
            $this->readWavHeader();
        } catch (WavFileException $ex) {
            $this->closeWav();
            throw $ex;
        }

        if ($readData) return $this->readWavData();

        return $this;
    }

    /**
     * Parse a wav header.
     * http://www-mmsp.ece.mcgill.ca/documents/audioformats/wave/wave.html
     *
     * @throws WavFormatException
     * @throws WavFileException
     */
    protected function readWavHeader()
    {
        if (!is_resource($this->_fp)) {
            throw new WavFileException('No wav file open. Use openWav() first.');
        }

        // get actual file size
        $stat = fstat($this->_fp);
        $actualSize = $stat['size'];

        $this->_actualSize = $actualSize;


        // read the common header
        $header = fread($this->_fp, 36);  // minimum size of the wav header
        if (strlen($header) < 36) {
            throw new WavFormatException('Not wav format. Header too short.', 1);
        }


        // check "RIFF" header
        $RIFF = unpack('NChunkID/VChunkSize/NFormat', $header);

        if ($RIFF['ChunkID'] != 0x52494646) {  // "RIFF"
            throw new WavFormatException('Not wav format. "RIFF" signature missing.', 2);
        }

        if ($actualSize - 8 < $RIFF['ChunkSize']) {
            trigger_error('"RIFF" chunk size does not match actual file size. Found ' . $RIFF['ChunkSize'] . ', expected ' . ($actualSize - 8) . '.', E_USER_NOTICE);
            $RIFF['ChunkSize'] = $actualSize - 8;
            //throw new WavFormatException('"RIFF" chunk size does not match actual file size. Found ' . $RIFF['ChunkSize'] . ', expected ' . ($actualSize - 8) . '.', 3);
        }

        if ($RIFF['Format'] != 0x57415645) {  // "WAVE"
            throw new WavFormatException('Not wav format. "RIFF" chunk format is not "WAVE".', 4);
        }

        $this->_chunkSize = $RIFF['ChunkSize'];


        // check common "fmt " subchunk
        $fmt = unpack('NSubchunkID/VSubchunkSize/vAudioFormat/vNumChannels/'
                     .'VSampleRate/VByteRate/vBlockAlign/vBitsPerSample',
                     substr($header, 12));

        if ($fmt['SubchunkID'] != 0x666d7420) {  // "fmt "
            throw new WavFormatException('Bad wav header. Expected "fmt " subchunk.', 11);
        }

        if ($fmt['SubchunkSize'] < 16) {
            throw new WavFormatException('Bad "fmt " subchunk size.', 12);
        }

        if (   $fmt['AudioFormat'] != self::WAVE_FORMAT_PCM
            && $fmt['AudioFormat'] != self::WAVE_FORMAT_IEEE_FLOAT
            && $fmt['AudioFormat'] != self::WAVE_FORMAT_EXTENSIBLE)
        {
            throw new WavFormatException('Unsupported audio format. Only PCM or IEEE FLOAT (EXTENSIBLE) audio is supported.', 13);
        }

        if ($fmt['NumChannels'] < 1 || $fmt['NumChannels'] > self::MAX_CHANNEL) {
            throw new WavFormatException('Invalid number of channels in "fmt " subchunk.', 14);
        }

        if ($fmt['SampleRate'] < 1 || $fmt['SampleRate'] > self::MAX_SAMPLERATE) {
            throw new WavFormatException('Invalid sample rate in "fmt " subchunk.', 15);
        }

        if (   ($fmt['AudioFormat'] == self::WAVE_FORMAT_PCM && !in_array($fmt['BitsPerSample'], array(8, 16, 24)))
            || ($fmt['AudioFormat'] == self::WAVE_FORMAT_IEEE_FLOAT && $fmt['BitsPerSample'] != 32)
            || ($fmt['AudioFormat'] == self::WAVE_FORMAT_EXTENSIBLE && !in_array($fmt['BitsPerSample'], array(8, 16, 24, 32))))
        {
            throw new WavFormatException('Only 8, 16 and 24-bit PCM and 32-bit IEEE FLOAT (EXTENSIBLE) audio is supported.', 16);
        }

        $blockAlign = $fmt['NumChannels'] * $fmt['BitsPerSample'] / 8;
        if ($blockAlign != $fmt['BlockAlign']) {
            trigger_error('Invalid block align in "fmt " subchunk. Found ' . $fmt['BlockAlign'] . ', expected ' . $blockAlign . '.', E_USER_NOTICE);
            $fmt['BlockAlign'] = $blockAlign;
            //throw new WavFormatException('Invalid block align in "fmt " subchunk. Found ' . $fmt['BlockAlign'] . ', expected ' . $blockAlign . '.', 17);
        }

        $byteRate = $fmt['SampleRate'] * $blockAlign;
        if ($byteRate != $fmt['ByteRate']) {
            trigger_error('Invalid average byte rate in "fmt " subchunk. Found ' . $fmt['ByteRate'] . ', expected ' . $byteRate . '.', E_USER_NOTICE);
            $fmt['ByteRate'] = $byteRate;
            //throw new WavFormatException('Invalid average byte rate in "fmt " subchunk. Found ' . $fmt['ByteRate'] . ', expected ' . $byteRate . '.', 18);
        }

        $this->_fmtChunkSize  = $fmt['SubchunkSize'];
        $this->_audioFormat   = $fmt['AudioFormat'];
        $this->_numChannels   = $fmt['NumChannels'];
        $this->_sampleRate    = $fmt['SampleRate'];
        $this->_byteRate      = $fmt['ByteRate'];
        $this->_blockAlign    = $fmt['BlockAlign'];
        $this->_bitsPerSample = $fmt['BitsPerSample'];


        // read extended "fmt " subchunk data
        $extendedFmt = '';
        if ($fmt['SubchunkSize'] > 16) {
            // possibly handle malformed subchunk without a padding byte
            $extendedFmt = fread($this->_fp, $fmt['SubchunkSize'] - 16 + ($fmt['SubchunkSize'] & 1));  // also read padding byte
            if (strlen($extendedFmt) < $fmt['SubchunkSize'] - 16) {
                throw new WavFormatException('Not wav format. Header too short.', 1);
            }
        }


        // check extended "fmt " for EXTENSIBLE Audio Format
        if ($fmt['AudioFormat'] == self::WAVE_FORMAT_EXTENSIBLE) {
            if (strlen($extendedFmt) < 24) {
                throw new WavFormatException('Invalid EXTENSIBLE "fmt " subchunk size. Found ' . $fmt['SubchunkSize'] . ', expected at least 40.', 19);
            }

            $extensibleFmt = unpack('vSize/vValidBitsPerSample/VChannelMask/H32SubFormat', substr($extendedFmt, 0, 24));

            if (   $extensibleFmt['SubFormat'] != self::WAVE_SUBFORMAT_PCM
                && $extensibleFmt['SubFormat'] != self::WAVE_SUBFORMAT_IEEE_FLOAT)
            {
                throw new WavFormatException('Unsupported audio format. Only PCM or IEEE FLOAT (EXTENSIBLE) audio is supported.', 13);
            }

            if (   ($extensibleFmt['SubFormat'] == self::WAVE_SUBFORMAT_PCM && !in_array($fmt['BitsPerSample'], array(8, 16, 24)))
                || ($extensibleFmt['SubFormat'] == self::WAVE_SUBFORMAT_IEEE_FLOAT && $fmt['BitsPerSample'] != 32))
            {
                throw new WavFormatException('Only 8, 16 and 24-bit PCM and 32-bit IEEE FLOAT (EXTENSIBLE) audio is supported.', 16);
            }

            if ($extensibleFmt['Size'] != 22) {
                trigger_error('Invaid extension size in EXTENSIBLE "fmt " subchunk.', E_USER_NOTICE);
                $extensibleFmt['Size'] = 22;
                //throw new WavFormatException('Invaid extension size in EXTENSIBLE "fmt " subchunk.', 20);
            }

            if ($extensibleFmt['ValidBitsPerSample'] != $fmt['BitsPerSample']) {
                trigger_error('Invaid or unsupported valid bits per sample in EXTENSIBLE "fmt " subchunk.', E_USER_NOTICE);
                $extensibleFmt['ValidBitsPerSample'] = $fmt['BitsPerSample'];
                //throw new WavFormatException('Invaid or unsupported valid bits per sample in EXTENSIBLE "fmt " subchunk.', 21);
            }

            if ($extensibleFmt['ChannelMask'] != 0) {
                // count number of set bits - Hamming weight
                $c = (int)$extensibleFmt['ChannelMask'];
                $n = 0;
                while ($c > 0) {
                    $n += $c & 1;
                    $c >>= 1;
                }
                if ($n != $fmt['NumChannels'] || (((int)$extensibleFmt['ChannelMask'] | self::SPEAKER_ALL) != self::SPEAKER_ALL)) {
                    trigger_error('Invalid channel mask in EXTENSIBLE "fmt " subchunk. The number of channels does not match the number of locations in the mask.', E_USER_NOTICE);
                    $extensibleFmt['ChannelMask'] = 0;
                    //throw new WavFormatException('Invalid channel mask in EXTENSIBLE "fmt " subchunk. The number of channels does not match the number of locations in the mask.', 22);
                }
            }

            $this->_fmtExtendedSize    = strlen($extendedFmt);
            $this->_validBitsPerSample = $extensibleFmt['ValidBitsPerSample'];
            $this->_channelMask        = $extensibleFmt['ChannelMask'];
            $this->_audioSubFormat     = $extensibleFmt['SubFormat'];

        } else {
            $this->_fmtExtendedSize    = strlen($extendedFmt);
            $this->_validBitsPerSample = $fmt['BitsPerSample'];
            $this->_channelMask        = 0;
            $this->_audioSubFormat     = null;
        }


        // read additional subchunks until "data" subchunk is found
        $factSubchunk = array();
        $dataSubchunk = array();

        while (!feof($this->_fp)) {
            $subchunkHeader = fread($this->_fp, 8);
            if (strlen($subchunkHeader) < 8) {
                throw new WavFormatException('Missing "data" subchunk.', 101);
            }

            $subchunk = unpack('NSubchunkID/VSubchunkSize', $subchunkHeader);

            if ($subchunk['SubchunkID'] == 0x66616374) {        // "fact"
                // possibly handle malformed subchunk without a padding byte
                $subchunkData = fread($this->_fp, $subchunk['SubchunkSize'] + ($subchunk['SubchunkSize'] & 1));  // also read padding byte
                if (strlen($subchunkData) < 4) {
                    throw new WavFormatException('Invalid "fact" subchunk.', 102);
                }

                $factParams = unpack('VSampleLength', substr($subchunkData, 0, 4));
                $factSubchunk = array_merge($subchunk, $factParams);

            } elseif ($subchunk['SubchunkID'] == 0x64617461) {  // "data"
                $dataSubchunk = $subchunk;

                break;

            } elseif ($subchunk['SubchunkID'] == 0x7761766C) {  // "wavl"
                throw new WavFormatException('Wave List Chunk ("wavl" subchunk) is not supported.', 106);
            } else {
                // skip all other (unknown) subchunks
                // possibly handle malformed subchunk without a padding byte
                if ( $subchunk['SubchunkSize'] < 0
                  || fseek($this->_fp, $subchunk['SubchunkSize'] + ($subchunk['SubchunkSize'] & 1), SEEK_CUR) !== 0) {  // also skip padding byte
                    throw new WavFormatException('Invalid subchunk (0x' . dechex($subchunk['SubchunkID']) . ') encountered.', 103);
                }
            }
        }

        if (empty($dataSubchunk)) {
            throw new WavFormatException('Missing "data" subchunk.', 101);
        }


        // check "data" subchunk
        $dataOffset = ftell($this->_fp);
        if ($dataSubchunk['SubchunkSize'] < 0 || $actualSize - $dataOffset < $dataSubchunk['SubchunkSize']) {
            trigger_error('Invalid "data" subchunk size.', E_USER_NOTICE);
            $dataSubchunk['SubchunkSize'] = $actualSize - $dataOffset;
            //throw new WavFormatException('Invalid "data" subchunk size.', 104);
        }

        $this->_dataOffset     = $dataOffset;
        $this->_dataSize       = $dataSubchunk['SubchunkSize'];
        $this->_dataSize_fp    = $dataSubchunk['SubchunkSize'];
        $this->_dataSize_valid = false;
        $this->_samples        = '';


        // check "fact" subchunk
        $numBlocks = (int)($dataSubchunk['SubchunkSize'] / $fmt['BlockAlign']);

        if (empty($factSubchunk)) {  // construct fake "fact" subchunk
            $factSubchunk = array('SubchunkSize' => 0, 'SampleLength' => $numBlocks);
        }

        if ($factSubchunk['SampleLength'] != $numBlocks) {
            trigger_error('Invalid sample length in "fact" subchunk.', E_USER_NOTICE);
            $factSubchunk['SampleLength'] = $numBlocks;
            //throw new WavFormatException('Invalid sample length in "fact" subchunk.', 105);
        }

        $this->_factChunkSize = $factSubchunk['SubchunkSize'];
        $this->_numBlocks     = $factSubchunk['SampleLength'];


        return $this;

    }

    /**
     * Read the wav data from the file into the buffer.
     *
     * @param $dataOffset  (Optional) The byte offset to skip before starting to read. Must be a multiple of BlockAlign.
     * @param $dataSize  (Optional) The size of the data to read in bytes. Must be a multiple of BlockAlign. Defaults to all data.
     * @throws WavFileException
     */
    public function readWavData($dataOffset = 0, $dataSize = null)
    {
        // check preconditions
        if (!is_resource($this->_fp)) {
            throw new WavFileException('No wav file open. Use openWav() first.');
        }

        if ($dataOffset < 0 || $dataOffset % $this->getBlockAlign() > 0) {
            throw new WavFileException('Invalid data offset. Has to be a multiple of BlockAlign.');
        }

        if (is_null($dataSize)) {
            $dataSize = $this->_dataSize_fp - ($this->_dataSize_fp % $this->getBlockAlign());  // only read complete blocks
        } elseif ($dataSize < 0 || $dataSize % $this->getBlockAlign() > 0) {
            throw new WavFileException('Invalid data size to read. Has to be a multiple of BlockAlign.');
        }


        // skip offset
        if ($dataOffset > 0 && fseek($this->_fp, $dataOffset, SEEK_CUR) !== 0) {
            throw new WavFileException('Seeking to data offset failed.');
        }

        // read data
        $this->_samples .= fread($this->_fp, $dataSize);  // allow appending
        $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()

        // close file or memory stream
        return $this->closeWav();
    }


    /*%******************************************************************************************%*/
    // Sample manipulation methods

    /**
     * Return a single sample block from the file.
     *
     * @param int $blockNum  (Required) The sample block number. Zero based.
     * @return string  The binary sample block (all channels). Returns null if the sample block number was out of range.
     */
    public function getSampleBlock($blockNum)
    {
        // check preconditions
        if (!$this->_dataSize_valid) {
            $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()
        }

        $offset = $blockNum * $this->_blockAlign;
        if ($offset + $this->_blockAlign > $this->_dataSize || $offset < 0) {
            return null;
        }


        // read data
        return substr($this->_samples, $offset, $this->_blockAlign);
    }

    /**
     * Set a single sample block. <br />
     * Allows to append a sample block.
     *
     * @param string $sampleBlock  (Required) The binary sample block (all channels).
     * @param int $blockNum  (Required) The sample block number. Zero based.
     * @throws WavFileException
     */
    public function setSampleBlock($sampleBlock, $blockNum)
    {
        // check preconditions
        $blockAlign = $this->_blockAlign;
        if (!isset($sampleBlock[$blockAlign - 1]) || isset($sampleBlock[$blockAlign])) {  // faster than: if (strlen($sampleBlock) != $blockAlign)
            throw new WavFileException('Incorrect sample block size. Got ' . strlen($sampleBlock) . ', expected ' . $blockAlign . '.');
        }

        if (!$this->_dataSize_valid) {
            $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()
        }

        $numBlocks = (int)($this->_dataSize / $blockAlign);
        $offset = $blockNum * $blockAlign;
        if ($blockNum > $numBlocks || $blockNum < 0) {  // allow appending
            throw new WavFileException('Sample block number is out of range.');
        }


        // replace or append data
        if ($blockNum == $numBlocks) {
            // append
            $this->_samples    .= $sampleBlock;
            $this->_dataSize   += $blockAlign;
            $this->_chunkSize  += $blockAlign;
            $this->_actualSize += $blockAlign;
            $this->_numBlocks++;
        } else {
            // replace
            for ($i = 0; $i < $blockAlign; ++$i) {
                $this->_samples[$offset + $i] = $sampleBlock[$i];
            }
        }

        return $this;
    }

    /**
     * Get a float sample value for a specific sample block and channel number.
     *
     * @param int $blockNum  (Required) The sample block number to fetch. Zero based.
     * @param int $channelNum  (Required) The channel number within the sample block to fetch. First channel is 1.
     * @return float  The float sample value. Returns null if the sample block number was out of range.
     * @throws WavFileException
     */
    public function getSampleValue($blockNum, $channelNum)
    {
        // check preconditions
        if ($channelNum < 1 || $channelNum > $this->_numChannels) {
            throw new WavFileException('Channel number is out of range.');
        }

        if (!$this->_dataSize_valid) {
            $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()
        }

        $sampleBytes = $this->_bitsPerSample / 8;
        $offset = $blockNum * $this->_blockAlign + ($channelNum - 1) * $sampleBytes;
        if ($offset + $sampleBytes > $this->_dataSize || $offset < 0) {
            return null;
        }

        // read binary value
        $sampleBinary = substr($this->_samples, $offset, $sampleBytes);

        // convert binary to value
        switch ($this->_bitsPerSample) {
            case 8:
                // unsigned char
                return (float)((ord($sampleBinary) - 0x80) / 0x80);

            case 16:
                // signed short, little endian
                $data = unpack('v', $sampleBinary);
                $sample = $data[1];
                if ($sample >= 0x8000) {
                    $sample -= 0x10000;
                }
                return (float)($sample / 0x8000);

            case 24:
                // 3 byte packed signed integer, little endian
                $data = unpack('C3', $sampleBinary);
                $sample = $data[1] | ($data[2] << 8) | ($data[3] << 16);
                if ($sample >= 0x800000) {
                    $sample -= 0x1000000;
                }
                return (float)($sample / 0x800000);

            case 32:
                // 32-bit float
                $data = unpack('f', $sampleBinary);
                return (float)$data[1];

            default:
                return null;
        }
    }

    /**
     * Sets a float sample value for a specific sample block number and channel. <br />
     * Converts float values to appropriate integer values and clips properly. <br />
     * Allows to append samples (in order).
     *
     * @param float $sampleFloat  (Required) The float sample value to set. Converts float values and clips if necessary.
     * @param int $blockNum  (Required) The sample block number to set or append. Zero based.
     * @param int $channelNum  (Required) The channel number within the sample block to set or append. First channel is 1.
     * @throws WavFileException
     */
    public function setSampleValue($sampleFloat, $blockNum, $channelNum)
    {
        // check preconditions
        if ($channelNum < 1 || $channelNum > $this->_numChannels) {
            throw new WavFileException('Channel number is out of range.');
        }

        if (!$this->_dataSize_valid) {
            $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()
        }

        $dataSize = $this->_dataSize;
        $bitsPerSample = $this->_bitsPerSample;
        $sampleBytes = $bitsPerSample / 8;
        $offset = $blockNum * $this->_blockAlign + ($channelNum - 1) * $sampleBytes;
        if (($offset + $sampleBytes > $dataSize && $offset != $dataSize) || $offset < 0) { // allow appending
            throw new WavFileException('Sample block or channel number is out of range.');
        }


        // convert to value, quantize and clip
        if ($bitsPerSample == 32) {
            $sample = $sampleFloat < -1.0 ? -1.0 : ($sampleFloat > 1.0 ? 1.0 : $sampleFloat);
        } else {
            $p = 1 << ($bitsPerSample - 1); // 2 to the power of _bitsPerSample divided by 2

            // project and quantize (round) float to integer values
            $sample = $sampleFloat < 0 ? (int)($sampleFloat * $p - 0.5) : (int)($sampleFloat * $p + 0.5);

            // clip if necessary to [-$p, $p - 1]
            if ($sample < -$p) {
                $sample = -$p;
            } elseif ($sample > $p - 1) {
                $sample = $p - 1;
            }
        }

        // convert to binary
        switch ($bitsPerSample) {
            case 8:
                // unsigned char
                $sampleBinary = chr($sample + 0x80);
                break;

            case 16:
                // signed short, little endian
                if ($sample < 0) {
                    $sample += 0x10000;
                }
                $sampleBinary = pack('v', $sample);
                break;

            case 24:
                // 3 byte packed signed integer, little endian
                if ($sample < 0) {
                    $sample += 0x1000000;
                }
                $sampleBinary = pack('C3', $sample & 0xff, ($sample >>  8) & 0xff, ($sample >> 16) & 0xff);
                break;

            case 32:
                // 32-bit float
                $sampleBinary = pack('f', $sample);
                break;

            default:
                $sampleBinary = null;
                $sampleBytes = 0;
                break;
        }

        // replace or append data
        if ($offset == $dataSize) {
            // append
            $this->_samples    .= $sampleBinary;
            $this->_dataSize   += $sampleBytes;
            $this->_chunkSize  += $sampleBytes;
            $this->_actualSize += $sampleBytes;
            $this->_numBlocks = (int)($this->_dataSize / $this->_blockAlign);
        } else {
            // replace
            for ($i = 0; $i < $sampleBytes; ++$i) {
                $this->_samples{$offset + $i} = $sampleBinary{$i};
            }
        }

        return $this;
    }


    /*%******************************************************************************************%*/
    // Audio processing methods

    /**
     * Run samples through audio processing filters.
     *
     * <code>
     * $wav->filter(
     *      array(
     *          WavFile::FILTER_MIX => array(          // Filter for mixing 2 WavFile instances.
     *              'wav' => $wav2,                    // (Required) The WavFile to mix into this WhavFile. If no optional arguments are given, can be passed without the array.
     *              'loop' => true,                    // (Optional) Loop the selected portion (with warping to the beginning at the end).
     *              'blockOffset' => 0,                // (Optional) Block number to start mixing from.
     *              'numBlocks' => null                // (Optional) Number of blocks to mix in or to select for looping. Defaults to the end or all data for looping.
     *          ),
     *          WavFile::FILTER_NORMALIZE => 0.6,      // (Required) Normalization of (mixed) audio samples - see threshold parameter for normalizeSample().
     *          WavFile::FILTER_DEGRADE => 0.9         // (Required) Introduce random noise. The quality relative to the amplitude. 1 = no noise, 0 = max. noise.
     *      ),
     *      0,                                         // (Optional) The block number of this WavFile to start with.
     *      null                                       // (Optional) The number of blocks to process.
     *  );
     *  </code>
     *
     * @param array $filters  (Required) An array of 1 or more audio processing filters.
     * @param int $blockOffset  (Optional) The block number to start precessing from.
     * @param int $numBlocks  (Optional) The maximum  number of blocks to process.
     * @throws WavFileException
     */
    public function filter($filters, $blockOffset = 0, $numBlocks = null)
    {
        // check preconditions
        $totalBlocks = $this->getNumBlocks();
        $numChannels = $this->getNumChannels();
        if (is_null($numBlocks)) $numBlocks = $totalBlocks - $blockOffset;

        if (!is_array($filters) || empty($filters) || $blockOffset < 0 || $blockOffset > $totalBlocks || $numBlocks <= 0) {
            // nothing to do
            return $this;
        }

        // check filtes
        $filter_mix = false;
        if (array_key_exists(self::FILTER_MIX, $filters)) {
            if (!is_array($filters[self::FILTER_MIX])) {
                // assume the 'wav' parameter
                $filters[self::FILTER_MIX] = array('wav' => $filters[self::FILTER_MIX]);
            }

            $mix_wav = @$filters[self::FILTER_MIX]['wav'];
            if (!($mix_wav instanceof WavFile)) {
                throw new WavFileException("WavFile to mix is missing or invalid.");
            } elseif ($mix_wav->getSampleRate() != $this->getSampleRate()) {
                throw new WavFileException("Sample rate of WavFile to mix does not match.");
            } else if ($mix_wav->getNumChannels() != $this->getNumChannels()) {
                throw new WavFileException("Number of channels of WavFile to mix does not match.");
            }

            $mix_loop = @$filters[self::FILTER_MIX]['loop'];
            if (is_null($mix_loop)) $mix_loop = false;

            $mix_blockOffset = @$filters[self::FILTER_MIX]['blockOffset'];
            if (is_null($mix_blockOffset)) $mix_blockOffset = 0;

            $mix_totalBlocks = $mix_wav->getNumBlocks();
            $mix_numBlocks = @$filters[self::FILTER_MIX]['numBlocks'];
            if (is_null($mix_numBlocks)) $mix_numBlocks = $mix_loop ? $mix_totalBlocks : $mix_totalBlocks - $mix_blockOffset;
            $mix_maxBlock = min($mix_blockOffset + $mix_numBlocks, $mix_totalBlocks);

            $filter_mix = true;
        }

        $filter_normalize = false;
        if (array_key_exists(self::FILTER_NORMALIZE, $filters)) {
            $normalize_threshold = @$filters[self::FILTER_NORMALIZE];

            if (!is_null($normalize_threshold) && abs($normalize_threshold) != 1) $filter_normalize = true;
        }

        $filter_degrade = false;
        if (array_key_exists(self::FILTER_DEGRADE, $filters)) {
            $degrade_quality = @$filters[self::FILTER_DEGRADE];
            if (is_null($degrade_quality)) $degrade_quality = 1;

            if ($degrade_quality >= 0 && $degrade_quality < 1) $filter_degrade = true;
        }


        // loop through all sample blocks
        for ($block = 0; $block < $numBlocks; ++$block) {
            // loop through all channels
            for ($channel = 1; $channel <= $numChannels; ++$channel) {
                // read current sample
                $currentBlock = $blockOffset + $block;
                $sampleFloat = $this->getSampleValue($currentBlock, $channel);


                /************* MIX FILTER ***********************/
                if ($filter_mix) {
                    if ($mix_loop) {
                        $mixBlock = ($mix_blockOffset + ($block % $mix_numBlocks)) % $mix_totalBlocks;
                    } else {
                        $mixBlock = $mix_blockOffset + $block;
                    }

                    if ($mixBlock < $mix_maxBlock) {
                        $sampleFloat += $mix_wav->getSampleValue($mixBlock, $channel);
                    }
                }

                /************* NORMALIZE FILTER *******************/
                if ($filter_normalize) {
                    $sampleFloat = $this->normalizeSample($sampleFloat, $normalize_threshold);
                }

                /************* DEGRADE FILTER *******************/
                if ($filter_degrade) {
                    $sampleFloat += rand(1000000 * ($degrade_quality - 1), 1000000 * (1 - $degrade_quality)) / 1000000;
                }


                // write current sample
                $this->setSampleValue($sampleFloat, $currentBlock, $channel);
            }
        }

        return $this;
    }

    /**
     * Append a wav file to the current wav. <br />
     * The wav files must have the same sample rate, number of bits per sample, and number of channels.
     *
     * @param WavFile $wav  (Required) The wav file to append.
     * @throws WavFileException
     */
    public function appendWav(WavFile $wav) {
        // basic checks
        if ($wav->getSampleRate() != $this->getSampleRate()) {
            throw new WavFileException("Sample rate for wav files do not match.");
        } else if ($wav->getBitsPerSample() != $this->getBitsPerSample()) {
            throw new WavFileException("Bits per sample for wav files do not match.");
        } else if ($wav->getNumChannels() != $this->getNumChannels()) {
            throw new WavFileException("Number of channels for wav files do not match.");
        }

        $this->_samples .= $wav->_samples;
        $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()

        return $this;
    }

    /**
     * Mix 2 wav files together. <br />
     * Both wavs must have the same sample rate and same number of channels.
     *
     * @param WavFile $wav  (Required) The WavFile to mix.
     * @param float $normalizeThreshold  (Optional) See normalizeSample for an explanation.
     * @throws WavFileException
     */
    public function mergeWav(WavFile $wav, $normalizeThreshold = null) {
        return $this->filter(array(
            WavFile::FILTER_MIX       => $wav,
            WavFile::FILTER_NORMALIZE => $normalizeThreshold
        ));
    }

    /**
     * Add silence to the wav file.
     *
     * @param float $duration  (Optional) How many seconds of silence. If negative, add to the beginning of the file. Defaults to 1s.
     */
    public function insertSilence($duration = 1.0)
    {
        $numSamples  = $this->getSampleRate() * abs($duration);
        $numChannels = $this->getNumChannels();

        $data = str_repeat(self::packSample($this->getZeroAmplitude(), $this->getBitsPerSample()), $numSamples * $numChannels);
        if ($duration >= 0) {
            $this->_samples .= $data;
        } else {
            $this->_samples = $data . $this->_samples;
        }

        $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()

        return $this;
    }

    /**
     * Degrade the quality of the wav file by introducing random noise.
     *
     * @param float quality  (Optional) The quality relative to the amplitude. 1 = no noise, 0 = max. noise.
     */
    public function degrade($quality = 1.0)
    {
        return $this->filter(self::FILTER_DEGRADE, array(
            WavFile::FILTER_DEGRADE => $quality
        ));
    }

    /**
     * Generate noise at the end of the wav for the specified duration and volume.
     *
     * @param float $duration  (Optional) Number of seconds of noise to generate.
     * @param float $percent  (Optional) The percentage of the maximum amplitude to use. 100 = full amplitude.
     */
    public function generateNoise($duration = 1.0, $percent = 100)
    {
        $numChannels = $this->getNumChannels();
        $numSamples  = $this->getSampleRate() * $duration;
        $minAmp      = $this->getMinAmplitude();
        $maxAmp      = $this->getMaxAmplitude();
        $bitDepth    = $this->getBitsPerSample();

        for ($s = 0; $s < $numSamples; ++$s) {
            if ($bitDepth == 32) {
                $val = rand(-$percent * 10000, $percent * 10000) / 1000000;
            } else {
                $val = rand($minAmp, $maxAmp);
                $val = (int)($val * $percent / 100);
            }

            $this->_samples .= str_repeat(self::packSample($val, $bitDepth), $numChannels);
        }

        $this->setDataSize();  // implicit setSize(), setActualSize(), setNumBlocks()

        return $this;
    }

    /**
     * Convert sample data to different bits per sample.
     *
     * @param int $bitsPerSample  (Required) The new number of bits per sample;
     * @throws WavFileException
     */
    public function convertBitsPerSample($bitsPerSample) {
        if ($this->getBitsPerSample() == $bitsPerSample) {
            return $this;
        }

        $tempWav = new WavFile($this->getNumChannels(), $this->getSampleRate(), $bitsPerSample);
        $tempWav->filter(
            array(self::FILTER_MIX => $this),
            0,
            $this->getNumBlocks()
        );

        $this->setSamples()                       // implicit setDataSize(), setSize(), setActualSize(), setNumBlocks()
             ->setBitsPerSample($bitsPerSample);  // implicit setValidBitsPerSample(), setAudioFormat(), setAudioSubFormat(), setFmtChunkSize(), setFactChunkSize(), setSize(), setActualSize(), setDataOffset(), setByteRate(), setBlockAlign(), setNumBlocks()
        $this->_samples = $tempWav->_samples;
        $this->setDataSize();                     // implicit setSize(), setActualSize(), setNumBlocks()

        return $this;
    }


    /*%******************************************************************************************%*/
    // Miscellaneous methods

    /**
     * Output information about the wav object.
     */
    public function displayInfo()
    {
        $s = "File Size: %u\n"
            ."Chunk Size: %u\n"
            ."fmt Subchunk Size: %u\n"
            ."Extended fmt Size: %u\n"
            ."fact Subchunk Size: %u\n"
            ."Data Offset: %u\n"
            ."Data Size: %u\n"
            ."Audio Format: %s\n"
            ."Audio SubFormat: %s\n"
            ."Channels: %u\n"
            ."Channel Mask: 0x%s\n"
            ."Sample Rate: %u\n"
            ."Bits Per Sample: %u\n"
            ."Valid Bits Per Sample: %u\n"
            ."Sample Block Size: %u\n"
            ."Number of Sample Blocks: %u\n"
            ."Byte Rate: %uBps\n";

        $s = sprintf($s, $this->getActualSize(),
                         $this->getChunkSize(),
                         $this->getFmtChunkSize(),
                         $this->getFmtExtendedSize(),
                         $this->getFactChunkSize(),
                         $this->getDataOffset(),
                         $this->getDataSize(),
                         $this->getAudioFormat() == self::WAVE_FORMAT_PCM ? 'PCM' : ($this->getAudioFormat() == self::WAVE_FORMAT_IEEE_FLOAT ? 'IEEE FLOAT' : 'EXTENSIBLE'),
                         $this->getAudioSubFormat() == self::WAVE_SUBFORMAT_PCM ? 'PCM' : 'IEEE FLOAT',
                         $this->getNumChannels(),
                         dechex($this->getChannelMask()),
                         $this->getSampleRate(),
                         $this->getBitsPerSample(),
                         $this->getValidBitsPerSample(),
                         $this->getBlockAlign(),
                         $this->getNumBlocks(),
                         $this->getByteRate());

        if (php_sapi_name() == 'cli') {
            return $s;
        } else {
            return nl2br($s);
        }
    }
}


/*%******************************************************************************************%*/
// Exceptions

/**
 * WavFileException indicates an illegal state or argument in this class.
 */
class WavFileException extends Exception {}

/**
 * WavFormatException indicates a malformed or unsupported wav file header.
 */
class WavFormatException extends WavFileException {}