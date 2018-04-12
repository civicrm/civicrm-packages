<?php
namespace CRM\GitFootnote;

/**
 */
abstract class AbstractWordFilter implements Filter {

    /**
     * @param \CRM\GitFootnote\CommitMessage $message
     */
    public function filter(CommitMessage $message) {
    $filter = $this;
    $words = $this->parseWords($message->getMessage());
    $wordsLen = count($words);
    for ($i = 0; $i < $wordsLen; $i += 2) {
      $words[$i] = $this->filterWord($message, $words[$i]);
    }
    $message->setMessage(implode($words));
  }

    /**
     * @param $messageText
     *
     * @return array[]|false|string[]
     */
    public function parseWords($messageText) {
    return preg_split('/([ ,;:\/\"\'\<\>!\?\.\(\)\[\]\r\n\t]+)/', $messageText, -1, PREG_SPLIT_DELIM_CAPTURE);
  }

    /**
     * @param \CRM\GitFootnote\CommitMessage $message
     * @param                                $word
     *
     * @return mixed
     */
    public abstract function filterWord(CommitMessage $message, $word);
}
