<?php
namespace CRM\GitFootnote;

/**
 * Interface Filter
 *
 * @package CRM\GitFootnote
 */
interface Filter {

    /**
     * Filter a commit message
     *
     * @param \CRM\GitFootnote\CommitMessage $message
     *
     * @return void
     */
  public function filter(CommitMessage $message);
}
