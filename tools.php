<?php

function array_rand_value($array, $num_req = 1)
{
    $idx = array_rand($array, $num_req);

    if(is_array($idx))
    {
        foreach($idx as &$i)
            $i = $array[$i];

        return $idx;
    }

    else
        return $array[$idx];

}

function array_slice_rand($array, $count)
{
    $acount = count($array);
    $max = max(0, $acount - $count);
    $offset = mt_rand(0, $max);

    return array_slice($array, $offset, $count);
}

function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}
