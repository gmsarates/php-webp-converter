<?php

class Converter
{
    private $discovered_files = [];
    private $path = null;

    public function _readFolder($path)
    {
        $files = array_diff(scandir($path), array('.', '..'));
        if (sizeof($files) > 0) {
            foreach ($files as $index => $file) {
                if (str_ends_with($path, '/')) $path = substr($path, 0, strlen($path) - 1);

                if (is_dir("$path/$file")) {
                    $this->_readFolder("$path/$file");
                } else {
                    $this->discovered_files[] = "$path/$file";
                }
            }
        }

        return $this->discovered_files;
    }

    public static function convert($files, $remover_originais = false, $compression_quality = 80)
    {
        $output = [];

        if (sizeof($files) > 0) {
            foreach ($files as $file) {
                try {
                    if (!file_exists($file)) {
                        return false;
                    }

                    $file_type = exif_imagetype($file);

                    $parts = explode('.', $file);
                    $ext = $parts[sizeof($parts) - 1];
                    $output_file =  str_replace($ext, 'webp', $file);

                    if (file_exists($output_file)) {
                        $output[] = [
                            'status' => 'error',
                            'file'   => $file,
                            'error'  => 'The file has already been converted'
                        ];

                        continue;
                    }

                    if (function_exists('imagewebp')) {
                        switch ($file_type) {
                            case '1': //IMAGETYPE_GIF
                                $image = imagecreatefromgif($file);
                                break;
                            case '2': //IMAGETYPE_JPEG
                                $image = imagecreatefromjpeg($file);
                                break;
                            case '3': //IMAGETYPE_PNG
                                $image = imagecreatefrompng($file);
                                imagepalettetotruecolor($image);
                                imagealphablending($image, true);
                                imagesavealpha($image, true);
                                break;
                            case '6': // IMAGETYPE_BMP
                                $image = imagecreatefrombmp($file);
                                break;
                            case '15': //IMAGETYPE_Webp
                                $output[] = [
                                    'status' => 'error',
                                    'file'   => $file,
                                    'error'  => 'File is already in webp format'
                                ];
                                break;
                            case '16': //IMAGETYPE_XBM
                                $image = imagecreatefromxbm($file);
                                break;
                            default:
                                $output[] = [
                                    'status' => 'error',
                                    'file'   => $file,
                                    'error'  => 'Unknown format'
                                ];
                        }

                        // Save the image
                        if (!isset($image)) {
                            $output[] = [
                                'status' => 'error',
                                'file'   => $file,
                                'error'  => 'Unable to save file #1'
                            ];
                            continue;
                        }

                        $result = imagewebp($image, $output_file, $compression_quality);
                        if (false === $result) {
                            $output[] = [
                                'status' => 'error',
                                'file'   => $file,
                                'error'  => 'Unable to save file #2'
                            ];
                            continue;
                        }

                        // Free up memory
                        imagedestroy($image);

                        if ($remover_originais) {
                            unlink($file);
                        }

                        $output[] = [
                            'status'      => 'success',
                            'file'        => $file,
                            'output_file' => $output_file,
                        ];
                        // return $output_file;
                    }

                    continue;
                } catch (\Throwable $th) {
                    $output[] = [
                        'status' => 'error',
                        'file'   => $file,
                        'error'  => 'Exception: ' . $th->getMessage()
                    ];
                }
            }
        }

        return $output;
    }

    public function init($args = null)
    {
        $options = [];
        if (!is_null($args) && is_array($args) && sizeof($args) > 1) {
            unset($args[0]);
            foreach ($args as $arg) {
                try {
                    $a = explode('=', $arg);
    
                    match ($a[0]) {
                        '--path', '-P' => $options['path'] = $a[1],
                        '--yes', '-Y' => $options['yes'] = true,
                        '--delete', '-D' => $options['delete'] = true,
                    };
                } catch (\Throwable $th) {}
            }
        }

        if (isset($options['path']) && trim($options['path']) != '') {
            $this->path = $options['path'];
        } else {
            $input_path = readline("Enter the path of the images: ");
            if (trim($input_path) != '') $this->path = $input_path;
        }

        if (is_null($this->path)) {
            Console::log("Please insert a path to scan", 'red');
            return false;
        }

        if (!is_dir($this->path)) {
            Console::log("Path '{$this->path}' does not exist", 'red');
            return false;
        }

        if (!str_ends_with($this->path, '/')) $this->path = $this->path . '/';

        $files = self::_readFolder($this->path);

        Console::log("Files found in {$this->path}:", 'blue');
        if (sizeof($files) > 0) {
            foreach ($files as $file) {
                Console::log($file, 'green');
            }
        }

        $confirmation = 'n';
        if (isset($options['yes']) && $options['yes']) {
            $confirmation = 'y';
            Console::log('Confirmation skipped', 'blue');
        } else {
            Console::log('Do you want to convert all?');
            $confirmation = readline('[y/N]: ');
        }

        if (strtolower($confirmation) == 'y') {
            $remove = 'n';
            if (isset($options['delete']) && $options['delete']) {
                $remove = 'y';
                Console::log('Deleting all files', 'blue');
            } else {
                Console::log('Do you want to delete the original files?', 'red');
                $remove = (strtolower(readline('[y/N]: ')) == 'y') ? true : false;
            }
            
            $result = self::convert($files, $remove);

            if (sizeof($result) > 0) {
                foreach ($result as $r) {
                    if ($r['status'] == 'error') {
                        Console::log("Error converting the file '{$r['file']}': {$r['error']}", 'red');
                    } else {
                        Console::log("Successful conversion of the file '{$r['file']}'", 'green');
                    }
                }
            }
        }
    }
}
