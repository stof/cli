<?php

/*
 * This file is part of the Puli CLI package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Cli\Json;

use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Reads JSON files and validates them against their JSON schema.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonReader
{
    /**
     * Reads and validates a JSON file.
     *
     * @param string $path       The path to the JSON file.
     * @param string $schemaPath The path to the JSON schema file.
     *
     * @return mixed The content of the JSON file.
     *
     * @throws JsonReaderException If the file could not be read or validation
     *                             failed.
     */
    public function readJson($path, $schemaPath)
    {
        if (!file_exists($path)) {
            throw new JsonReaderException(sprintf(
                'The file "%s" does not exist.',
                $path
            ));
        }

        $jsonData = $this->readJsonData($path);

        $this->validateJsonData($jsonData, $path, $schemaPath);

        return $jsonData;
    }

    private function readJsonData($path)
    {
        $contents = file_get_contents($path);
        $jsonData = json_decode($contents);

        // Data could not be decoded
        if (null === $jsonData && null !== $contents) {
            $parser = new JsonParser();
            $e = $parser->lint($jsonData);

            // No idea if there's a case where this can happen
            if (!$e instanceof ParsingException) {
                throw new JsonReaderException(sprintf(
                    'The file "%s" does not contain valid JSON.',
                    $path
                ));
            }

            throw new JsonReaderException(sprintf(
                'The file "%s" does not contain valid JSON: %s',
                $path,
                $e->getMessage()
            ), 0, $e);
        }

        return $jsonData;
    }

    private function validateJsonData($jsonData, $path, $schemaPath)
    {
        if (!file_exists($schemaPath)) {
            throw new JsonReaderException(sprintf(
                'The schema file "%s" does not exist.',
                $schemaPath
            ));
        }

        $schema = json_decode(file_get_contents($schemaPath));

        $validator = new Validator();
        $validator->check($jsonData, $schema);

        if (!$validator->isValid()) {
            $errors = '';

            foreach ((array) $validator->getErrors() as $error) {
                $prefix = $error['property'] ? $error['property'].': ' : '';
                $errors .= "\n".$prefix.$error['message'];
            }

            throw new JsonReaderException(sprintf(
                "The file \"%s\" does not match the defined JSON schema:%s",
                $path,
                $errors
            ));
        }
    }
}