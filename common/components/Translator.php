<?php

namespace common\components;

use common\models\SystemDictionary;
use Yii;
use yii\helpers\ArrayHelper;

class Translator
{
    private static $_instance;
    private static $wordBaseLatCy = [];

    public function __construct()
    {
        //self::$wordBaseLatCy = ArrayHelper::map(SystemDictionary::find()->all(), 'latin', 'cyrill');
        foreach (self::$wordBaseLatCy as $lat => $cy) {
            self::$wordBaseLatCy[strtolower($lat)] = mb_convert_case($cy, MB_CASE_LOWER, 'UTF-8');
            self::$wordBaseLatCy[strtoupper($lat)] = mb_convert_case($cy, MB_CASE_UPPER, 'UTF-8');
            self::$wordBaseLatCy[ucfirst($lat)] = mb_convert_case($cy, MB_CASE_TITLE, 'UTF-8');
        }
    }


    /**
     * @return Translator
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Translator();
        }
        return self::$_instance;
    }

    const LANG_CY = 'cy';
    const LANG_UZ = 'uz';

    public static $letters = [
        "o'" => "o‘",
        "o`" => "o‘",
        "o’" => "o‘",
        "o&rsquo;" => "o‘",
        "o&lsquo;" => "o‘",
        "O'" => "O‘",
        "O`" => "O‘",
        "O’" => "O‘",
        "O&rsquo;" => "O‘",
        "O&lsquo;" => "O‘",
        "g'" => "g‘",
        "g`" => "g‘",
        "g’" => "g‘",
        "g&rsquo;" => "g‘",
        "g&lsquo;" => "g‘",
        "G'" => "G‘",
        "G`" => "G‘",
        "G’" => "G‘",
        "G&rsquo;" => "G‘",
        "G&lsquo;" => "G‘",
        "`" => "’",
        "'" => "’",
        "&lsquo;" => "’",
        "&rsquo;" => "’",
    ];


    protected $charMapCy = [
        "a" => "а",
        "b" => "б",
        "c" => "s",
        "d" => "д",
        "e" => "е",
        "f" => "ф",
        "g" => "г",
        "h" => "ҳ",
        "i" => "и",
        "j" => "ж",
        "k" => "к",
        "l" => "л",
        "m" => "м",
        "n" => "н",
        "o" => "о",
        "p" => "п",
        "q" => "қ",
        "r" => "р",
        "s" => "с",
        "t" => "т",
        "u" => "у",
        "v" => "в",
        "w" => "в",
        "x" => "х",
        "y" => "й",
        "z" => "з",
    ];
    protected $charMapUz = [
        "а" => "a",
        "б" => "b",
        "д" => "d",
        "е" => "e",
        "ф" => "f",
        "г" => "g",
        "ҳ" => "h",
        "и" => "i",
        "ж" => "j",
        "к" => "k",
        "л" => "l",
        "м" => "m",
        "н" => "n",
        "о" => "o",
        "п" => "p",
        "қ" => "q",
        "р" => "r",
        "с" => "s",
        "т" => "t",
        "у" => "u",
        "в" => "v",
        "х" => "x",
        "й" => "y",
        "з" => "z",
        "ш" => "sh",
        "щ" => "sh",
        "ч" => "ch",
        "я" => "ya",
        "ё" => "yo",
        "ю" => "yu",
        "ў" => "o‘",
        "ғ" => "g‘",
        "ъ" => "’",
        "ц" => "ts",
        "э" => "e",
        "ь" => "",
        "ы" => "i",
    ];
    protected $lang;


    public function translateToCyrillic($value)
    {
        if (is_string($value)) {
            return $this->_translateToCyrillic($value);
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->_translateToCyrillic($item);
            }
            return $result;
        }

        return $value;
    }

    public function translateToSlug($value, $replacement = '-')
    {
        return trim(preg_replace('/[^A-Za-z0-9-_]+/', $replacement, strtolower($this->translateToLatin($value))), $replacement);
    }

    public function translateToLatin($value)
    {
        if (is_string($value)) {
            return $this->_translateToLatin($value);
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->_translateToLatin($item);
            }
            return $result;
        }

        return $value;
    }

    private function _translateToCyrillic($content)
    {
        $content = html_entity_decode($content);

        $content = $this->replaceFullWords($content, $replaceMap);
        $hasReplaces = $replaceMap && count($replaceMap);

        $pos = -1;
        $length = mb_strlen($content);
        $tagOpen = false;
        $upperCase = false;
        $translation = "";
        $replaceChar = "";

        $prevChar = "";
        $char = "";
        $nextChar = "";
        $apos = "'`‘’";

        while ($pos < $length) {
            $pos++;
            $translation .= $replaceChar;

            $prevChar = $char;
            $char = mb_substr($content, $pos, 1);


            if ($char == '>' || $char == ']' || $char == '}') {
                $tagOpen = false;
            }

            if ($char == '<' || $char == '[' || $char == '{') {
                $tagOpen = true;
            }

            if ($tagOpen) {
                $replaceChar = $char;
                continue;
            }

            $prevChar = $pos > 0 ? mb_strtolower(mb_substr($content, $pos - 1, 1)) : ' ';
            $nextChar = $pos + 1 < $length ? mb_strtolower(mb_substr($content, $pos + 1, 1)) : false;
            $upperCase = mb_strtoupper($char) == $char;

            $char = mb_strtolower($char);

            if ($nextChar) {
                if ($nextChar == 's') {
                    if ($char == 't') {
                        $replaceChar = $upperCase ? 'Ц' : 'ц';
                        $pos++;
                        continue;
                    }
                }

                if ($nextChar == 'h') {
                    if ($char == 'c') {
                        $replaceChar = $upperCase ? 'Ч' : 'ч';
                        $pos++;
                        continue;
                    }
                    if ($char == 's') {
                        $replaceChar = $upperCase ? 'Ш' : 'ш';
                        $pos++;
                        continue;
                    }
                }
                if (mb_strpos($apos, $nextChar) !== false) {
                    if ($char == 'o') {
                        $replaceChar = $upperCase ? 'Ў' : 'ў';
                        $pos++;
                        continue;
                    }
                    if ($char == 'g') {
                        $replaceChar = $upperCase ? 'Ғ' : 'ғ';
                        $pos++;
                        continue;
                    }
                }

                if ($char == 'y') {
                    if ($nextChar == 'e' || $nextChar == 'е') {
                        $replaceChar = $upperCase ? 'Е' : 'е';
                        $pos++;
                        continue;
                    }

                    if ($nextChar == 'a' || $nextChar == 'а') {
                        $replaceChar = $upperCase ? 'Я' : 'я';
                        $pos++;
                        continue;
                    }

                    if ($nextChar == 'o' || $nextChar == 'о') {
                        $nnChar = isset($content[$pos + 2]) ? mb_strtolower(mb_substr($content, $pos + 2, 1)) : false;
                        if ($nnChar && mb_strpos($apos, $nnChar) === false) {
                            $replaceChar = $upperCase ? 'Ё' : 'ё';
                            $pos++;
                            continue;
                        }
                    }
                    if ($nextChar == 'u') {
                        $replaceChar = $upperCase ? 'Ю' : 'ю';
                        $pos++;
                        continue;
                    }

                }

                if ($char == 'e' && !isset($this->charMapCy[$prevChar])) {
                    $replaceChar = $upperCase ? 'Э' : 'э';
                    continue;
                }

                if (mb_strpos($apos, $char) !== false && isset($this->charMapCy[$nextChar])) {
                    $replaceChar = 'ъ';
                    continue;
                }
            }


            if (isset($this->charMapCy[$char])) {
                $replaceChar = $upperCase ? mb_strtoupper($this->charMapCy[$char]) : $this->charMapCy[$char];
                continue;
            }

            $replaceChar = $upperCase ? mb_strtoupper($char) : $char;
        }
        $translation .= $replaceChar;

        return $translation;
    }


    private function _translateToLatin($content)
    {
        $content = html_entity_decode($content);
        $content = $this->replaceFullWords($content, $replaceMap, false);

        $pos = -1;
        $length = mb_strlen($content);
        $tagOpen = false;
        $upperCase = false;
        $translation = "";
        $replaceChar = "";

        $prevChar = "";
        $char = "";
        $nextChar = "";

        while ($pos < $length) {
            $pos++;
            $translation .= $replaceChar;
            $char = mb_substr($content, $pos, 1);

            if ($char == '>' || $char == ']' || $char == '}') {
                $tagOpen = false;
            }

            if ($char == '<' || $char == '[' || $char == '}') {
                $tagOpen = true;
            }

            if ($tagOpen) {
                $replaceChar = $char;
                continue;
            }


            $prevChar = isset($content[$pos - 1]) ? mb_strtolower(mb_substr($content, $pos - 1, 1)) : ' ';
            $nextChar = isset($content[$pos + 1]) ? mb_strtolower(mb_substr($content, $pos + 1, 1)) : false;
            $upperCase = mb_strtoupper($char) == $char;

            $char = mb_strtolower($char);
            if ($char == 'ц') {
                if (mb_strpos("аиоеуъ", $prevChar) !== false) {
                    $replaceChar = $upperCase ? 'Ts' : 'ts';
                } else {
                    $replaceChar = $upperCase ? 'S' : 's';
                }
                continue;
            }

            if ($char == 'е') {
                if (mb_strpos("аиоеуёяъ", $prevChar) !== false || !isset($this->charMapUz[$prevChar])) {
                    $replaceChar = $upperCase ? 'Ye' : 'ye';
                } else {
                    $replaceChar = $upperCase ? 'E' : 'e';
                }
                continue;
            }


            if (isset($this->charMapUz[$char])) {
                if ($upperCase) {
                    if (mb_strlen($this->charMapUz[$char]) == 2) {
                        $replaceChar = mb_strtoupper(substr($this->charMapUz[$char], 0, 1)) . substr($this->charMapUz[$char], 1);
                    } else {
                        $replaceChar = mb_strtoupper($this->charMapUz[$char]);
                    }
                } else {
                    $replaceChar = $this->charMapUz[$char];
                }
                continue;
            }

            $replaceChar = $upperCase ? mb_strtoupper($char) : $char;
        }

        $translation .= $replaceChar;
        return $translation;
    }

    private function replaceFullWords($content, &$map, $latinToCyrill = true)
    {
        $i = 0;

        if ($latinToCyrill) {
            foreach (self::$wordBaseLatCy as $latin => $cyrill) {
                $newContent = preg_replace('/' . $latin . '/u', $cyrill, $content);

                if ($newContent != $content) {
                    $map[$i] = $cyrill;
                }

                $content = $newContent;
                $i++;
            }
        } else {
            foreach (self::$wordBaseLatCy as $latin => $cyrill) {
                $newContent = preg_replace('/' . $cyrill . '/u', $latin, $content);

                if ($newContent != $content) {
                    $map[$i] = $latin;
                }

                $content = $newContent;
                $i++;
            }
        }
        return $content;
    }
}