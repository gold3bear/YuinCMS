<?php

class helper {

    /**
     * 清除XSS
     * 
     * @param  $key 字符
     * @param  $low 过滤等级,true为低
     * @return mixed
     */
    static function clean_xss(&$string, $low = False) {
        if (!is_array($string)) {
            $string = trim($string);
            $string = strip_tags($string);
            $string = htmlspecialchars($string);
            if ($low) {
                return True;
            }
            $string = str_replace(array('"', "\\", "'", "/", "..", "../", "./", "//"), '', $string);
            $no     = '/%0[0-8bcef]/';
            $string = preg_replace($no, '', $string);
            $no     = '/%1[0-9a-f]/';
            $string = preg_replace($no, '', $string);
            $no     = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace($no, '', $string);
            return True;
        }
        $keys = array_keys($string);
        foreach ($keys as $key) {
            clean_xss($string [$key]);
        }
    }

    /**
     *  创建多级目录
     *
     *
     *  @access public
     *  @return void
     */
    static function mkdirs($dir) {
        if (!is_dir($dir)) {
            if (!self::mkdirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0755)) {
                return false;
            }
        }
        return true;
    }

    /**
     *  获取扩展名
     *
     *  最科学的获取扩展名的方法
     *
     *  @access public
     *  @return void
     */
    static function getExtension($file) {
        $tempfile = @fopen($file, "rb");
        $bin      = fread($tempfile, 2); //只读2字节  
        fclose($tempfile);
        $strInfo  = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
        $fileType = '';
        switch ($typeCode) {
// 6677:bmp 255216:jpg 7173:gif 13780:png 7790:exe 8297:rar 8075:zip tar:109121 7z:55122 gz 31139
            case '255216':
                $fileType = 'jpg';
                break;
            case '7173':
                $fileType = 'gif';
                break;
            case '13780':
                $fileType = 'png';
                break;
            case '8075':
                $fileType = 'apk';
                break;
            default:
        }
        return $fileType;
    }
    
    static function themes() {
        $a_dir        = Config::get('view.paths');
        $theme_dir    = dir(array_pop($a_dir));
        $exist_themes = array();
        while (false !== ($file         = $theme_dir->read())) {
            if (trim($file, '.') && is_dir($theme_dir->path . '/' . $file)) {
                if (strtolower(substr($file, -7)) != '_mobile' && strtolower(substr($file, -4)) != '.bak') {
                    $exist_themes[] = $file;
                }
            }
        }
        return $exist_themes;
    }

}
