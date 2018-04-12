<?php
namespace CRM\GitFootnote;

/**
 * Remove any lines with a leading "#"
 */
class CommentFilter implements Filter {

    /**
     * @param \CRM\GitFootnote\CommitMessage $message
     */
    public function filter(CommitMessage $message) {
    $lines = explode("\n", $message->getMessage());
    $lines = array_filter($lines, function ($line) {
      if (empty($line)) {
        return TRUE;
      }
      if ($line{0} != '#') {
        return TRUE;
      }
      return FALSE;
    });
    $message->setMessage(implode("\n", $lines));
  }

}
