<?php

namespace chornij\console;

/**
 * Class XmlHelper
 * @package chornij\console
 */
class XmlHelper
{

    /**
     * @var bool Display error in format output
     */
    public $displayErrors = false;

    /**
     * @var string XML text for processing
     */
    private $xmlText;

    /**
     * @var string Formatted XML text
     */
    private $formattedXml;

    /**
     * @var array XML errors
     */
    private $errors;

    /**
     * @param string $xmlText XML text
     */
    public function __construct($xmlText)
    {
        $this->xmlText = $xmlText;
    }

    /**
     * Formatting XML string
     *
     * @return string
     */
    public function format()
    {
        if (empty($this->xmlText)) {
            $this->formattedXml = $this->xmlText;
        } else {
            $dom = new \DOMDocument();

            if ($this->isValid()) {
                $dom->preserveWhiteSpace = false;
                $dom->loadXML($this->xmlText);
                $dom->formatOutput = true;

                $this->formattedXml = $dom->saveXML();
            } elseif ($this->displayErrors) {
                $errors = '<!-- There are XML syntax errors: -->' . PHP_EOL;

                foreach (self::getErrors($this->xmlText) as $key => $error) {
                    $errors .= '    <!-- #' . ($key + 1) . ' on ' . $error->line . ':' . $error->column . ' - `' . trim($error->message) . '` -->' . PHP_EOL;
                }

                $text = $errors . PHP_EOL . $this->xmlText;

                $this->formattedXml = $text;
            } else {
                $this->formattedXml = $this->xmlText;
            }
        }

        return $this->formattedXml;
    }

    /**
     * Getting formatted XML text
     *
     * @return string
     */
    public function getFormattedText()
    {
        return $this->format();
    }

    /**
     * Check if XML valid
     *
     * @param string|null $text XML text
     *
     * @return bool
     */
    public function isValid($text = null)
    {
        $text = is_null($text) ? $this->xmlText : $text;

        return count($this->getErrors($text)) == 0;
    }

    /**
     * Get XML errors
     *
     * @param string|null $text XML text
     *
     * @return \LibXMLError[]|array
     */
    private function getErrors($text = null)
    {
        if (is_null($this->errors)) {
            $text = is_null($text) ? $this->xmlText : $text;

            libxml_use_internal_errors(true);

            $doc = simplexml_load_string($text);

            $this->errors = !$doc ? libxml_get_errors() : [];
        }

        return $this->errors;
    }
}
