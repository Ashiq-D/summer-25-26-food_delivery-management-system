<?php

function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);

    return $data;
}
