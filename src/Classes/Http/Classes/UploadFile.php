<?php

namespace YonisSavary\Sharp\Classes\Http\Classes;

use YonisSavary\Sharp\Classes\Env\Storage;

/**
 * Class to modelize Upload Files, methods that are the most useful are
 * - `move()`: Attempt to move the temp file to a new destination
 * - `getFailReason()`: Get the reason behind any `move()` fail
 * - `getName()`: Get the file basename
 * - `getType()`: Get the file MIME type
 * - `getError()`: Get the file PHP's error code
 * - `getSize()`: Get the file size (bytes)
 * - `getInputName()`: Get the file input name
 * - `getExtension()`: Get the file extension (dot-less)
 * - `getNewName()`: Get the file new basename
 * - `getNewPath()`: Get the file new path
 */
class UploadFile
{
    const KB = 1024;
    const MB = 1024**2;
    const GB = 1024**3;

    /** Target directory does not exists */
    const REASON_OK = 1<<0 ;

    /** Destination file already exists */
    const REASON_ALREADY_EXISTING_FILE = 1<<1 ;

    /** The file could not be moved (`rename()` fail) */
    const REASON_FAILED_RENAME = 1<<2 ;

    /** The new file does not have the same size as the origin */
    const REASON_INVALID_NEW_SIZE = 1<<3 ;

    /** Target directory is not writable ! */
    const REASON_DIRECTORY_NOT_WRITABLE = 1<<4;

    /** There was an error with PHP Upload, see `getError()` for more (https://www.php.net/manual/en/features.file-upload.errors.php)*/
    const REASON_PHP_UPLOAD_ERROR = 1<<5;

    protected string $name;
    protected string $type;
    protected string $tempName;
    protected int $error;
    protected int $size;
    protected string $inputName;
    protected string $extension;

    protected string $newName;
    protected string $newPath;

    protected bool $wasMoved = false;

    protected int $failReason = self::REASON_OK;

    /**
     * @param array $data Data from PHP $_FILES
     * @param string $inputName Input name key from $_FILES
     */
    public function __construct(array $data, string $inputName='uploads')
    {
        // PHP's $_FILES data
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->tempName = $data['tmp_name'];
        $this->error = $data['error'];
        $this->size = $data['size'];

        // Extras info
        $this->inputName = $inputName;
        $this->extension = pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * @return bool Can the temp file be moved ?
     */
    public function movable(): bool
    {
        if ($this->isMoved())
            return false;

        if ($this->error !== UPLOAD_ERR_OK)
            return false;

        return true;
    }

    /**
     * Attempt to make a unique name for the moved file
     * (Collision should be quite rare)
     */
    protected function makeUniqueName(): string
    {
        return uniqid('upload-'.date('YmdHis').'-') .'.'.$this->extension ;
    }

    protected function failWithReason(int $reason): bool
    {
        $this->failReason = $reason;
        return false;
    }

    /**
     * Try to move the file to a new directory, return `true` on success or `false` on failure
     *
     * @param string|Storage $destination Either a target directory name (relative to Storage directory), or a Storage object
     * @param string $newName New name of the file, a name is generated if null is given
     * @return string|false The new file path on success, `false` on fail, see `getFailReason()` to get the reason behind a failure
     */
    public function move(string|Storage $destination, string $newName=null): string|false
    {
        if ($this->isMoved())
            return false;

        $this->failReason = self::REASON_OK;

        $this->newName = $newName ?? $this->makeUniqueName();

        if (is_string($destination))
            $destinationStorage = Storage::getInstance()->getSubStorage($destination);
        else
            $destinationStorage = $destination;

        $this->newPath = $destinationStorage->path($this->newName);

        if ($this->error !== UPLOAD_ERR_OK)
            return $this->failWithReason(self::REASON_PHP_UPLOAD_ERROR);

        if (!is_writable($destinationStorage->getRoot()))
            return $this->failWithReason(self::REASON_DIRECTORY_NOT_WRITABLE);

        if ($destinationStorage->isFile($this->newName))
            return $this->failWithReason(self::REASON_ALREADY_EXISTING_FILE);

        if (!rename($this->tempName, $this->newPath))
            return $this->failWithReason(self::REASON_FAILED_RENAME);

        if (!$destinationStorage->isFile($this->newName))
            return $this->failWithReason(self::REASON_FAILED_RENAME);

        if (filesize($this->newPath) != $this->size)
            return $this->failWithReason(self::REASON_INVALID_NEW_SIZE);

        $this->wasMoved = true;
        return $this->newPath;
    }

    /**
     * @return bool Was the file successfully moved ?
     */
    public function isMoved(): bool
    {
        return $this->wasMoved;
    }

    /**
     * @return string original file basename
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string temp file path
     */
    public function getTempName(): string
    {
        return $this->tempName;
    }

    /**
     * @return string file MIME type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int File's error code (`$_FILES`, https://www.php.net/manual/en/features.file-upload.errors.php)
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return int File's size in bytes
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int File's HTML input name
     */
    public function getInputName(): string
    {
        return $this->inputName;
    }

    /**
     * @return string File's extension (dot-less)
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return ?string New File basename, `null` if not moved yet
     */
    public function getNewName(): ?string
    {
        return $this->newName;
    }

    /**
     * @return ?string New File path, `null` if not moved yet
     */
    public function getNewPath(): ?string
    {
        return $this->newPath;
    }

    /**
     * @return ?int Get the reason behind a `move()` failure, `null` if there was no error
     */
    public function getFailReason(): ?int
    {
        return $this->failReason;
    }
}