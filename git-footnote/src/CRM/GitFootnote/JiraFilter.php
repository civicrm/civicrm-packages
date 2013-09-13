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

  public function filter(CommitMessage $message) {
    $words = $this->parseWords(trim($message->getMessage(), "\r\n\t "));
    if (count($words) == 1) {
      $message->setMessage($this->filterStandaloneWord($words[0]));
    } else {
      parent::filter($message);
    }
  }

  /**
   * Given a single-word commit, filter the one word
   *
   * @param $word
   * @return string
   */
  public function filterStandaloneWord($word) {
    if (preg_match($this->wordPattern, $word)) {
      $issue = $this->getIssue($word);
      if ($issue) {
        return ($word . ' - ' . $issue->getSummary() . "\n\n" . $this->createIssueUrl($word));
      }
      else {
        return ($word . ' - ' . $this->createIssueUrl($word));
      }
    }
    return $word;
  }

  /**
   * Filter each word in the commit message separately.
   *
   * @param CommitMessage $message
   * @param $word
   * @return mixed
   */
  public function filterWord(CommitMessage $message, $word) {
    if (preg_match($this->wordPattern, $word)) {
      $issue = $this->getIssue($word);
      if ($issue) {
        $title = $word . ': ' . $issue->getSummary();
      } else {
        $title = $word . ':';
      }
      $message->addLinkNote($this->createIssueUrl($word), $title);
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

  /**
   * @param string $issueKey
   * @return string
   */
  protected function createIssueUrl($issueKey) {
    return $this->url . '/browse/' . $issueKey;
  }
}
