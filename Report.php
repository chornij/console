<?php

namespace chornij\console;

/**
 * Class Report
 * @package chornij\console
 */
class Report
{
    const FOREGROUND_INDEX = 38;
    const BACKGROUND_INDEX = 48;
    const COLOR256_REGEXP = '~^(bg_)?color_([0-9]{1,3})$~';

    /**
     * @var array Titles writing default styles
     */
    public $titleStyles = [
        'bold',
        'underline',
    ];

    /**
     * @var array Default message writing styles
     */
    public $defaultStyles = [];

    /**
     * @var bool Display XML errors in output
     */
    public $displayXmlErrors = false;

    /**
     * @var bool Force supporting 256 color for terminals
     */
    public $forceSupport256Color = false;

    public $xmlAngleStyle = 'cyan';
    public $xmlTagStyle = 'cyan';
    public $xmlTagValueStyle = 'light_gray';
    public $xmlAttributeStyle = ['blue', 'bold', 'italic'];
    public $xmlEqualSignStyle = 'light_gray';
    public $xmlAttributeValueStyle = ['magenta', 'italic'];

    /**
     * @var array default srtyles
     */
    private $styles = [
        'none' => null,
        'bold' => '1',
        'dark' => '2',
        'italic' => '3',
        'underline' => '4',
        'blink' => '5',
        'reverse' => '7',
        'concealed' => '8',

        'default' => '39',
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'light_gray' => '37',

        'dark_gray' => '90',
        'light_red' => '91',
        'light_green' => '92',
        'light_yellow' => '93',
        'light_blue' => '94',
        'light_magenta' => '95',
        'light_cyan' => '96',
        'white' => '97',

        'bg_default' => '49',
        'bg_black' => '40',
        'bg_red' => '41',
        'bg_green' => '42',
        'bg_yellow' => '43',
        'bg_blue' => '44',
        'bg_magenta' => '45',
        'bg_cyan' => '46',
        'bg_light_gray' => '47',

        'bg_dark_gray' => '100',
        'bg_light_red' => '101',
        'bg_light_green' => '102',
        'bg_light_yellow' => '103',
        'bg_light_blue' => '104',
        'bg_light_magenta' => '105',
        'bg_light_cyan' => '106',
        'bg_white' => '107',
    ];

    /**
     * Write title
     *
     * @param string $text Title text
     *
     * @return string
     */
    public function title($text)
    {
        return $this->colorize($text, $this->titleStyles) . PHP_EOL;
    }

    /**
     * Writing sub title
     *
     * @param string $text Subtitle text
     *
     * @return string
     */
    public function subtitle($text)
    {
        return $this->colorize($text, $this->titleStyles) . PHP_EOL;
    }

    /**
     * Write message
     *
     * @param string $text Message test
     * @param array $styles Additional styles
     * @param bool $addNewLine Adding new line
     *
     * @return string
     */
    public function write($text, $styles = [], $addNewLine = true)
    {
        if (count($styles) == 0) {
            $styles = $this->defaultStyles;
        }

        $result = $this->colorize($text, $styles);

        return $addNewLine ? ($result . PHP_EOL) : $result;
    }

    /**
     * Write message in XML format
     *
     * @param string $text XML text
     * @param array|string|null $styles XML styles
     *
     * @return string
     */
    public function writeXml($text, $styles = null)
    {
        $xmlObject = new XmlHelper($text);
        $xmlObject->displayErrors = $this->displayXmlErrors;

        if (!is_null($styles)) {
            $xml = $this->colorize($xmlObject->getFormattedText(), $styles) . PHP_EOL;
        } else {
            $parser = xml_parser_create();

            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

            xml_parse_into_struct($parser, $xmlObject->getFormattedText(), $rows, $index);

            $xml = '';
            foreach ($rows as $row) {
                $open = $close = [];
                $value = '';

                switch ($row['type']) {
                    case 'open':
                        $open = [
                            $this->colorize('<', $this->xmlAngleStyle),
                            $this->colorize($row['tag'], $this->xmlTagStyle),
                            $this->colorize('>', $this->xmlAngleStyle),
                        ];
                        break;
                    case 'complete':
                        if (isset($row['value'])) {
                            $open = [
                                $this->colorize('<', $this->xmlAngleStyle),
                                $this->colorize($row['tag'], $this->xmlTagStyle),
                                $this->colorize('>', $this->xmlAngleStyle),
                            ];

                            if (isset($row['attributes'])) {
                                $attributes = '';
                                foreach ($row['attributes'] as $key => $value) {
                                    $attributes .= ' ' . $this->colorize($key, $this->xmlAttributeStyle) .
                                        $this->colorize('=', $this->xmlEqualSignStyle) .
                                        $this->colorize('"' . $value . '"', $this->xmlAttributeValueStyle);
                                }

                                $open[1] .= $attributes;
                            }

                            $value = $this->colorize($row['value'], $this->xmlTagValueStyle);
                            $close = [
                                $this->colorize('</', $this->xmlAngleStyle),
                                $this->colorize($row['tag'], $this->xmlTagStyle),
                                $this->colorize('>', $this->xmlAngleStyle),
                            ];
                        } else {
                            $open = [
                                $this->colorize('<', $this->xmlAngleStyle),
                                $this->colorize($row['tag'], $this->xmlTagStyle),
                                $this->colorize('/>', $this->xmlAngleStyle),
                            ];
                        }
                        break;
                    case 'close':
                        $close = [
                            $this->colorize('</', $this->xmlAngleStyle),
                            $this->colorize($row['tag'], $this->xmlTagStyle),
                            $this->colorize('>', $this->xmlAngleStyle),
                        ];
                        break;
                }

                $indent = $row['level'] > 1 ? str_repeat(' ', $row['level'] * 2) : null;

                $xml .= $indent . implode('', $open) . $value . implode('', $close) . PHP_EOL;
            }

            xml_parser_free($parser);
        }

        return $xml;
    }

    /**
     * Wrap text with ANSI/VT100 Control sequences
     *
     * @param string $value Text for wrap
     *
     * @return string
     */
    private function wrapColor($value)
    {
        return "\033[" . $value . 'm';
    }

    /**
     * Add color for text
     *
     * @param string $text Input text
     * @param array $styles Color styles
     *
     * @return string
     */
    public function colorize($text, $styles)
    {
        if (is_string($styles)) {
            $styles = [$styles];
        }

        $sequences = [];
        foreach ($styles as $style) {
            if ($this->isValidStyle($style)) {
                $sequences[] = $this->styleSequence($style);
            }
        }

        $sequences = array_filter($sequences, function ($val) {
            return !is_null($val);
        });

        if (count($sequences) == 0) {
            return $text;
        } else {
            return $this->wrapColor(implode(';', $sequences)) . $text . $this->wrapColor(0);
        }
    }

    /**
     * Validate style name
     *
     * @param string $style Style name
     *
     * @return bool
     */
    private function isValidStyle($style)
    {
        return array_key_exists($style, $this->styles) || preg_match(self::COLOR256_REGEXP, $style);
    }

    /**
     * Adding style
     *
     * @param string $style Style name
     *
     * @return null|string
     */
    private function styleSequence($style)
    {
        if (array_key_exists($style, $this->styles)) {
            return $this->styles[$style];
        } elseif (!$this->is256ColorsSupported()) {
            return null;
        }

        preg_match(self::COLOR256_REGEXP, $style, $matches);

        $type = $matches[1] === 'bg_' ? self::BACKGROUND_INDEX : self::FOREGROUND_INDEX;
        $value = $matches[2];

        return $type . ';5;' . $value;
    }

    /**
     * Is terminal support 256 color pallet
     *
     * @return bool
     */
    public function is256ColorsSupported()
    {
        if ($this->forceSupport256Color) {
            return true;
        } else {
            return DIRECTORY_SEPARATOR === '/' && strpos(getenv('TERM'), '256color') !== false;
        }
    }

    /**
     * Get predefined styles
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }
}
