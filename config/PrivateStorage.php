<?php

class PrivateStorage {

    public static function solicitudesDirectory() {

        $projectRoot = realpath(__DIR__ . '/..');

        if ($projectRoot === false) {
            return null;
        }

        return $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
    }

    public static function solicitudPath($filename) {

        if (
            !is_string($filename) ||
            !preg_match('/\A[a-f0-9]{32}\.(pdf|doc|docx|jpe?g|png|webp|mp4|mp3|wav|m4a)\z/i', $filename)
        ) {
            return null;
        }

        $directory = self::solicitudesDirectory();

        return $directory === null
            ? null
            : $directory . DIRECTORY_SEPARATOR . $filename;
    }
}