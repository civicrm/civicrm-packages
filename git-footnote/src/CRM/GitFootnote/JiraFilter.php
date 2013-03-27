<?php
namespace CRM\GitFootnote;

class JiraFilter extends AbstractWordFilter {

  protected $wordPattern;
  protected $url;
  protected $jiraApi;
  protected $jiraCache;

  /**
   * @param string $wordPattern
   * @param string $url
   * @param Jira_Api|NULL $jiraApi
   */
  public function __construct($wordPattern, $url, $jiraApi = NULL) {
    $this->wordPattern = $wordPattern;
    $this->url = $url;
    $this->jiraApi = $jiraApi;
    $this->jiraCache = array();
  }

  public function filterWord(CommitMessage $message, $word) {
    if (preg_match($this->wordPattern, $word)) {
      $issue = $this->getIssue($word);
      if ($issue) {
        $title = $word . ': ' . $issue->getSummary();
      } else {
        $title = $word . ':';
      }
      $message->addLinkNote($this->url . '/browse/' . $word, $title);
    }
    return $word;
  }

  /**
   * @return Jira_Issue|NULL|FALSE (NULL if no service available; FALSE if invalid key)
   */
  protected function getIssue($key) {
    if (! $this->jiraApi) {
      return NULL;
    }
    if (! isset($this->jiraCache[$key])) {
      $this->jiraCache[$key] = FALSE;
      if (!preg_match('/^[A-Za-z0-9\-]+$/', $key)) {
        throw new \Exception("Invalid JIRA key: $key");
      }

      $walker = new \Jira_Issues_Walker($this->jiraApi);
      $walker->push("key = $key", "*navigable");
      foreach ($walker as $k => $issue) {
        $this->jiraCache[$key] = $issue;
      }
    }
    return $this->jiraCache[$key];
  }
}
