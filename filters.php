<?php

    /**
     * Validité d'une chane de charactere au format Unix/WeB
     *
     * @param string $string
     * @return string
     */
    function filter_slug($string)
    {
        $search = array(
            "À", "Á", "Â", "Ã", "Ä", "Å", "à", "á", "â", "ã", "ä", "å",
            "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "ò", "ó", "ô", "õ", "ö", "ø",
            "È", "É", "Ê", "Ë", "è", "é", "ê", "ë",
            "Ç", "ç",
            "Ì", "Í", "Î", "Ï", "ì", "í", "î", "ï",
            "Ù", "Ú", "Û", "Ü", "ù", "ú", "û", "ü",
            "ÿ", "Ñ", "ñ");

        $replace = array(
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
            "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
            "e", "e", "e", "e", "e", "e", "e", "e",
            "c", "c",
            "i", "i", "i", "i", "i", "i", "i", "i",
            "u", "u", "u", "u", "u", "u", "u", "u",
            "y", "n", "n");

        $string = str_replace($search, $replace, $string);

        $todash = array("?", "!", "@", "#", "&percnt;", "&amp;", "*", "(", ")", "[", "]", "=", "+", " ", ";", ":", "'", ".", "_", '"');

        $string = mb_strtolower($string);

        $string = str_replace($todash, "-", $string);
        $string = preg_replace("#^-#", "", $string);
        $string = preg_replace("#-+#", "-", $string);
        $string = preg_replace("#[^a-z0-9-]#", "", $string);

        $string = htmlspecialchars($string);

        return $string;
    }

    function filter_basename($val)
    {
        return basename($val);
    }

    function filter_sha1($val)
    {
        return sha1($val);
    }
    
    function filter_md5($val)
    {
        return md5($val);
    }

    function filter_lower($val)
    {
        return strtolower($val);
    }
    
    function filter_upper($val)
    {
        return strtoupper($val);
    }
