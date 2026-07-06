<?php

namespace App\Core;

/**
 * Validator supporting the rule set used by the application.
 * Throws ValidationException on failure.
 */
class Validator
{
    protected array $data;
    protected array $files;
    protected array $rules;
    protected array $errors = [];

    public function __construct(array $data, array $rules, array $files = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->files = $files;
    }

    /**
     * Validate and return the validated subset of input.
     */
    public static function validate(array $data, array $rules, array $files = []): array
    {
        $validator = new self($data, $rules, $files);
        return $validator->run();
    }

    public function run(): array
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);

            if (str_ends_with($field, '.*')) {
                $this->validateArrayField(substr($field, 0, -2), $rules);
                continue;
            }

            $value = $this->getValue($field);
            $isFile = isset($this->files[$field]) && $this->fileProvided($this->files[$field]);

            // nullable: skip remaining rules when empty
            if (in_array('nullable', $rules, true) && $this->isEmpty($value) && !$isFile) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }
                $this->applyRule($field, $value, $rule, $isFile);
            }
        }

        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }

        return $this->validatedData();
    }

    protected function validateArrayField(string $field, array $rules): void
    {
        $values = $this->getValue($field);
        if (!is_array($values)) {
            return;
        }
        foreach ($values as $value) {
            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }
                $this->applyRule($field, $value, $rule, false);
            }
        }
    }

    protected function applyRule(string $field, $value, string $rule, bool $isFile): void
    {
        [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);
        $params = $parameter !== null ? explode(',', $parameter) : [];
        $label = $this->label($field);

        switch ($name) {
            case 'required':
                if ($isFile) {
                    if (!$this->fileProvided($this->files[$field])) {
                        $this->addError($field, "The {$label} field is required.");
                    }
                } elseif ($this->isEmpty($value)) {
                    $this->addError($field, "The {$label} field is required.");
                }
                break;

            case 'email':
                if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$label} must be a valid email address.");
                }
                break;

            case 'string':
                if (!$this->isEmpty($value) && !is_string($value)) {
                    $this->addError($field, "The {$label} must be a string.");
                }
                break;

            case 'integer':
                if (!$this->isEmpty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "The {$label} must be an integer.");
                }
                break;

            case 'boolean':
                if (!$this->isEmpty($value) && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                    $this->addError($field, "The {$label} field must be true or false.");
                }
                break;

            case 'array':
                if (!$this->isEmpty($value) && !is_array($value)) {
                    $this->addError($field, "The {$label} must be an array.");
                }
                break;

            case 'min':
                $this->checkSize($field, $value, (float) $params[0], 'min', $isFile, $label);
                break;

            case 'max':
                $this->checkSize($field, $value, (float) $params[0], 'max', $isFile, $label);
                break;

            case 'in':
                if (!$this->isEmpty($value) && !in_array((string) $value, $params, true)) {
                    $this->addError($field, "The selected {$label} is invalid.");
                }
                break;

            case 'date':
                if (!$this->isEmpty($value) && strtotime((string) $value) === false) {
                    $this->addError($field, "The {$label} is not a valid date.");
                }
                break;

            case 'after':
                $other = $this->getValue($params[0]);
                if (!$this->isEmpty($value) && !$this->isEmpty($other) && strtotime((string) $value) <= strtotime((string) $other)) {
                    $this->addError($field, "The {$label} must be a date after {$this->label($params[0])}.");
                }
                break;

            case 'after_or_equal':
                $other = $this->getValue($params[0]);
                if (!$this->isEmpty($value) && !$this->isEmpty($other) && strtotime((string) $value) < strtotime((string) $other)) {
                    $this->addError($field, "The {$label} must be a date after or equal to {$this->label($params[0])}.");
                }
                break;

            case 'unique':
                $this->checkUnique($field, $value, $params, $label);
                break;

            case 'exists':
                $this->checkExists($field, $value, $params, $label);
                break;

            case 'image':
                $this->checkImage($field, $label);
                break;

            case 'file':
                if (!$this->fileProvided($this->files[$field] ?? null)) {
                    $this->addError($field, "The {$label} must be a file.");
                }
                break;

            case 'mimes':
                $this->checkMimes($field, $params, $label);
                break;
        }
    }

    protected function checkSize(string $field, $value, float $size, string $mode, bool $isFile, string $label): void
    {
        if ($isFile) {
            $fileSizeKb = ($this->files[$field]['size'] ?? 0) / 1024;
            $fails = $mode === 'max' ? $fileSizeKb > $size : $fileSizeKb < $size;
            if ($fails) {
                $this->addError($field, "The {$label} may not be greater than {$size} kilobytes.");
            }
            return;
        }

        if ($this->isEmpty($value)) {
            return;
        }

        if (is_numeric($value)) {
            $fails = $mode === 'max' ? (float) $value > $size : (float) $value < $size;
            $unit = '';
        } else {
            $length = mb_strlen((string) $value);
            $fails = $mode === 'max' ? $length > $size : $length < $size;
            $unit = ' characters';
        }

        if ($fails) {
            $word = $mode === 'max' ? 'may not be greater than' : 'must be at least';
            $this->addError($field, "The {$label} {$word} {$size}{$unit}.");
        }
    }

    protected function checkUnique(string $field, $value, array $params, string $label): void
    {
        if ($this->isEmpty($value)) {
            return;
        }
        $table = $params[0];
        $column = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
        $bindings = [$value];
        if ($ignoreId !== null) {
            $sql .= " AND `id` != ?";
            $bindings[] = $ignoreId;
        }
        if ((int) Database::scalar($sql, $bindings) > 0) {
            $this->addError($field, "The {$label} has already been taken.");
        }
    }

    protected function checkExists(string $field, $value, array $params, string $label): void
    {
        if ($this->isEmpty($value)) {
            return;
        }
        $table = $params[0];
        $column = $params[1] ?? 'id';
        $count = (int) Database::scalar("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?", [$value]);
        if ($count === 0) {
            $this->addError($field, "The selected {$label} is invalid.");
        }
    }

    protected function checkImage(string $field, string $label): void
    {
        $file = $this->files[$field] ?? null;
        if (!$this->fileProvided($file)) {
            return;
        }
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            $this->addError($field, "The {$label} must be an image.");
        }
    }

    protected function checkMimes(string $field, array $params, string $label): void
    {
        $file = $this->files[$field] ?? null;
        if (!$this->fileProvided($file)) {
            return;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array_map('strtolower', $params), true)) {
            $this->addError($field, "The {$label} must be a file of type: " . implode(', ', $params) . '.');
        }
    }

    // --- Helpers ----------------------------------------------------------

    protected function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    protected function isEmpty($value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    protected function fileProvided($file): bool
    {
        return is_array($file)
            && isset($file['tmp_name'])
            && $file['tmp_name'] !== ''
            && (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK);
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    protected function label(string $field): string
    {
        return str_replace('_', ' ', $field);
    }

    protected function validatedData(): array
    {
        $validated = [];
        foreach ($this->rules as $field => $rules) {
            if (str_ends_with($field, '.*')) {
                continue;
            }
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }
}
