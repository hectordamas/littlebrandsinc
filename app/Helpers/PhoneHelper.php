<?php

if (!function_exists('format_phone')) {

    function format_phone($phone)
    {
        if (!$phone) return null;

        // Limpiar todo lo que no sea número
        $phone = preg_replace('/\D/', '', $phone);

        // Si es muy corto, devolverlo tal cual
        if (strlen($phone) < 10) {
            return '+' . $phone;
        }

        // Formato tipo internacional básico
        return '+' . substr($phone, 0, 2) . ' ' .
               substr($phone, 2, 3) . ' ' .
               substr($phone, 5, 3) . ' ' .
               substr($phone, 8);
    }
}