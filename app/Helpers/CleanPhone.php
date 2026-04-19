<?php

if (!function_exists('clean_phone')) {
    function clean_phone($phone)
    {
        return preg_replace('/\D/', '', $phone);
    }
}
