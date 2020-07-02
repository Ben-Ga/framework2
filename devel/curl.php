<?php
/**
 * Class to use Curl
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
/**
 * Curl class
 */
    class Curl
    {
        private static $lastcode = '';
        private static $lasterror = '';
        private static $lasterrno = '';
        private static $cf = '';

        private static function setup($url, $accept = '', $upw = '')
        {
            $ch = curl_init($url);
            if (self::$cf === '')
            {
                self::$cf = '/tmp/fw'.uniqid().'.txt';
            }
            curl_setopt_array($ch, [
                CURLOPT_USERAGENT       => 'Framework Test',
                CURLOPT_CONNECTTIMEOUT  => 30,
                CURLOPT_RETURNTRANSFER  => TRUE,
                CURLOPT_FOLLOWLOCATION  => TRUE,
                CURLOPT_MAXREDIRS       => 6,
                CURLOPT_COOKIESESSION   => TRUE,
                CURLOPT_COOKIEFILE      => self::$cf,
                CURLOPT_COOKIEJAR       => self::$cf,
                CURLOPT_SSL_VERIFYPEER  => FALSE,
#               CURLOPT_SSL_VERIFYHOST  => 2,
#               CURLOPT_CAINFO          => Local::$subdpath.'/cacert.pem',
            ]);

            if ($accept != '')
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: '.$accept]);
            }

            if ($upw != '')
            {
                curl_setopt($ch, CURLOPT_USERPWD, $upw);
            }
            return $ch;
        }

        private static function exec($ch)
        {
            if (($data = curl_exec($ch)) === FALSE)
            {
                $data = '';
            }
            self::$lasterror = curl_error($ch);
            self::$lasterrno = curl_errno($ch);
            self::$lastcode = curl_getinfo($ch);
            curl_close($ch);
            return $data;
        }

        public static function fetch($url, $accept = '', $upw = '')
        {
            $ch = self::setup($url, $accept, $upw);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            return self::exec($ch);
        }

        public static function head($url)
        {
            $ch = self::setup($url);
            curl_setopt($ch, CURLOPT_HEADER, TRUE); // header will be at output
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'
            return self::exec($ch);
        }

        public static function post($url, $data, $upw = '')
        {
            $encoded = [];
            foreach ($data as $key => $value)
            {
                $encoded[] = urlencode($key).'='.urlencode($value);
            }
            $ch = self::setup($url, '', $upw);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  implode('&', $encoded));
            return self::exec($ch);
        }

        public static function code()
        {
            return self::$lastcode;
        }

        public static function error()
        {
            return self::$lasterror;
        }

        public static function cleanup()
        {
            if (self::$cf !== '' && file_exists(self::$cf))
            {
                unlink(self::$cf);
            }
        }
    }
?>