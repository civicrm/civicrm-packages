<?php
namespace CRM\GitFootnote;

/**
 * Class CommitMessageTest
 *
 * @package CRM\GitFootnote
 */
class CommitMessageTest extends \PHPUnit_Framework_TestCase {
  public function testEmpty() {
    $message = new CommitMessage('');
    $this->assertEquals('', $message->toString());
  }

  public function testBasic() {
    $message = new CommitMessage("Hello\nworld");
    $this->assertEquals("Hello\nworld", $message->toString());
  }

  public function testOneLink() {
    $message = new CommitMessage("Hello\nworld\n");
    $message->addLinkNote('http://example.com', 'Example');
    $this->assertEquals(
'Hello
world

----------------------------------------
* Example
  http://example.com
', $message->toString());
  }

  public function testOneLink_noTitle() {
    $message = new CommitMessage("Hello\nworld\n");
    $message->addLinkNote('http://example.com');
    $this->assertEquals(
'Hello
world

----------------------------------------
* http://example.com
', $message->toString());
  }

  public function testMultipleLink() {
    $message = new CommitMessage("Hello\nworld\n");
    $message->addLinkNote('http://example.com', 'Example');
    $message->addLinkNote('http://example.org', 'Example For Good');
    $message->addLinkNote('http://example.com', 'Example Redundant');
    $this->assertEquals(
'Hello
world

----------------------------------------
* Example
  http://example.com
* Example For Good
  http://example.org
', $message->toString());
  }
}
