<?php
namespace CRM\GitFootnote;

/**
 */
abstract class AbstractWordFilter implements Filter {

  public function filter(CommitMessage $message) {
    $filter = $this;
    $words = preg_split('/([ ,;\"\'\<\>!\?\.\(\)\[\]\r\n\t]+)/', $message->getMessage(), -1, PREG_SPLIT_DELIM_CAPTURE);
    $wordsLen = count($words);
    for ($i = 0; $i < $wordsLen; $i += 2) {
      $words[$i] = $this->filterWord($message, $words[$i]);
    }
    $message->setMessage(implode($words));
  }

  public abstract function filterWord(CommitMessage $message, $word);
}
