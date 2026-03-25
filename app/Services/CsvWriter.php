<?php

namespace SearchTracker\Rus\Services;

class CsvWriter
{
    protected $path;
    protected $fileName;
    protected $filePath;
    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $file;
    protected $fileMode = 'w+';

    public function __construct()
    {
        $this->fileName = uniqid().'.csv';
        $this->path = $this->_getDir().'/_tempCSV';
        $this->_mkdir();
        $this->file = $this->_make_file();
    }

    public function _getDir()
    {
        $uploadDir = wp_upload_dir();

        return $uploadDir['basedir'] .'/'. SEARCH_TRACKER_ASSET_ID;
    }

    public function _mkdir()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function _make_file(){
        $this->filePath = $this->path.'/'.$this->fileName;
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }

        $f = fopen($this->filePath, $this->fileMode);

        if ($f === false) {
            die('Error opening the file ' . esc_html($this->filePath));
        }

        return $f;
    }

    public function insertOne($row){
        $this->file = fopen($this->filePath, 'a+');

        if ($this->file === false) {
            die('Error opening the file ' . esc_html($this->filePath));
        }

        fputcsv($this->file, $this->normalizeRow($row), $this->delimiter, $this->enclosure);
        fclose($this->file);
    }

    public function insertAll($data){
        $this->file = fopen($this->filePath, 'a+');

        if ($this->file === false) {
            die('Error opening the file ' . esc_html($this->filePath));
        }

        foreach ($data as $row) {
            fputcsv($this->file, $this->normalizeRow($row), $this->delimiter, $this->enclosure);
        }
        fclose($this->file);
    }

    protected function normalizeRow($row)
    {
        if (is_object($row)) {
            $row = get_object_vars($row);
        }

        if (!is_array($row)) {
            $row = [$row];
        }

        foreach ($row as $key => $value) {
            $row[$key] = $this->normalizeValue($value);
        }

        return $row;
    }

    protected function normalizeValue($value)
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return wp_json_encode($value);
    }

    public function output($filename)
    {
        if (!is_null($filename) && file_exists($this->filePath)) {
            $filename = preg_replace('/[\x00-\x1F]/', '', sanitize_file_name($filename));
            header('Cache-Control: private');
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Length: '.filesize($this->filePath));
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            //Read the size of the file
            readfile($this->filePath);
            unlink($this->filePath);
            die();
        }
        die('Error opening the file ' . esc_html($this->filePath));
    }
}
