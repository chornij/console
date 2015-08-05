<?php

namespace chornij\console;

/**
 * Class ReportTest
 *
 * <b>To run test:</b>
 *
 * phpunit --configuration phpunit.config.xml tests/ReportTest.php
 *
 * @package chornij\console
 */
class ReportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Report Report object
     */
    private $report;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->report = new Report();
    }

    /**
     * Test styles displaying
     */
    public function testStyles()
    {
        foreach ($this->report->getStyles() as $styleName => $styleCode) {
            if (is_null($styleCode)) {
                continue;
            }

            $text = '`' . $styleName . '` style';
            $message = $this->report->write($text, $styleName);

            $this->assertEquals("\033[" . $styleCode . 'm' . $text . "\033[0m" . PHP_EOL, $message);

            echo $message;
        }
    }

    /**
     * Testing title
     */
    public function testTitle()
    {
        $text = 'Testing title console writing';
        $message = $this->report->title($text);

        $this->assertEquals("\033[1;4m" . $text . "\033[0m" . PHP_EOL, $message);

        echo $message;
    }

    /**
     * Testing sub title
     */
    public function testSubTitle()
    {
        $text = 'Subtile testing';
        $message = $this->report->subtitle($text);

        $this->assertEquals("\033[1;4m" . $text . "\033[0m" . PHP_EOL, $message);

        echo $message;
    }

    /**
     * Testing simple messages
     */
    public function testMessage()
    {
        $text = 'Hello ';
        $message = $this->report->write($text, ['red'], false);
        $this->assertEquals("\033[" . 31 . 'm' . $text . "\033[0m", $message);
        echo $message;

        $text = 'World';
        $message = $this->report->write($text, ['green']);
        $this->assertEquals("\033[" . 32 . 'm' . $text . "\033[0m" . PHP_EOL, $message);
        echo $message;

        $text = 'Simple text';
        $message = $this->report->write($text);
        $this->assertEquals($text . PHP_EOL, $message);
        echo $message;

        $text = '<comment>some comment</comment>';
        $message = $this->report->write($text, 'blue');
        $this->assertEquals("\033[34m<comment>some comment</comment>\033[0m" . PHP_EOL, $message);
        echo $message;

        $text = 'Some text';
        $message = $this->report->write($text, ['blue', 'bg_green'], false);
        $this->assertEquals("\033[34;42m" . $text . "\033[0m", $message);
        echo $message;
    }

    /**
     * Test styles combining
     */
    public function testBoldAndDark()
    {
        $text = 'Text';

        $message = $this->report->write($text, ['bold', 'dark'], false);
        $this->assertEquals("\033[1;2m" . $text . "\033[0m", $message);
        echo $message;

        $this->report->forceSupport256Color = true;
        $message = $this->report->write($text, 'color_255', false);
        $this->assertEquals("\033[38;5;255m" . $text . "\033[0m", $message);
        echo $message;

        $this->report->forceSupport256Color = false;
        $message = $this->report->write($text, 'color_255', false);
        $this->assertEquals($text, $message);
        echo $message;
    }

    /**
     * Testing XML writing
     */
    public function testXmlWriting()
    {
        $text = '';
        $message = $this->report->writeXml($text, 'cyan');

        $this->assertEquals("\033[36m\033[0m" . PHP_EOL, $message);
        echo $message;

        $text = '<?xml version="1.0" encoding="UTF-8"?><request><body>Text</body></request>';
        $message = $this->report->writeXml($text, 'cyan');

        $this->assertEquals("\033[36m<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<request>
  <body>Text</body>
</request>
\033[0m" . PHP_EOL, $message);

        echo $message;
    }

    /**
     *
     */
    public function testColoredXml()
    {
        $text = '<?xml version="1.0" encoding="UTF-8"?>
<TransferReversalRequest>
  <ICA>123456</ICA>
  <TransactionReference>4239920003040253011</TransactionReference>
  <ReversalReason param="value">1111 IN PROCESSING</ReversalReason>
  <Code/>
</TransferReversalRequest>';
        $message = $this->report->writeXml($text);

        $this->assertEquals("\033[36m<\033[0m\033[36mTransferReversalRequest\033[0m\033[36m>\033[0m
    \033[36m<\033[0m\033[36mICA\033[0m\033[36m>\033[0m\033[37m123456\033[0m\033[36m</\033[0m\033[36mICA\033[0m\033[36m>\033[0m
    \033[36m<\033[0m\033[36mTransactionReference\033[0m\033[36m>\033[0m\033[37m4239920003040253011\033[0m\033[36m</\033[0m\033[36mTransactionReference\033[0m\033[36m>\033[0m
    \033[36m<\033[0m\033[36mReversalReason\033[0m \033[34;1;3mparam\033[0m\033[37m=\033[0m\033[35;3m\"value\"\033[0m\033[36m>\033[0m\033[37m1111 IN PROCESSING\033[0m\033[36m</\033[0m\033[36mReversalReason\033[0m\033[36m>\033[0m
    \033[36m<\033[0m\033[36mCode\033[0m\033[36m/>\033[0m
\033[36m</\033[0m\033[36mTransferReversalRequest\033[0m\033[36m>\033[0m" . PHP_EOL, $message);

        echo $message;
    }

    /**
     * Testing outputing invalid XML text
     */
    public function testInvalidXmlWriting()
    {
        $this->report->displayXmlErrors = true;

        $text = '<?xml version="1.0" encoding="UTF-8 "?><request><body>Text</body></request>';
        $message = $this->report->writeXml($text, 'cyan');

        $this->assertEquals("\033[36m<!-- There are XML syntax errors: -->
    <!-- #1 on 1:34 - `String not closed expecting \" or '` -->
    <!-- #2 on 1:34 - `parsing XML declaration: '?>' expected` -->

<?xml version=\"1.0\" encoding=\"UTF-8 \"?><request><body>Text</body></request>\033[0m" . PHP_EOL, $message);
        echo $message;

        $this->report->displayXmlErrors = false;

        $text = '<?xml version="1.0" encoding="UTF-8 "?><request><body>Text</body></request>';
        $message = $this->report->writeXml($text, 'cyan');
        $this->assertEquals("\033[36m<?xml version=\"1.0\" encoding=\"UTF-8 \"?><request><body>Text</body></request>\033[0m" . PHP_EOL, $message);

        echo $message;
    }
}
