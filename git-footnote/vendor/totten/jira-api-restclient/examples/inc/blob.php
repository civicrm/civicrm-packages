<?php

/**
 * Class Blob
 */
class Blob
{
    protected $db;

    public function __construct()
    {
        $db = new Pdo("mysql:host=localhost; dbname=jira_notification","root","");
        //$db->exec("CREATE TABLE IF NOT EXISTS blob (id int unsigned, updated_at datetime, primary key name)");
        //create table notification (id int unsigned, updated_at datetime, primary key(id)) engine = innodb;
        $this->db = $db;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function get($name)
    {
        $stmt = $this->db->prepare("select * from notification where id = :id");
        $stmt->bindValue(":id", $name);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @param $status
     */
    public function insert($id, $status)
    {
        $stmt = $this->db->prepare("insert ignore into notification(id,updated_at) values(:id, :status)");
        $stmt->bindValue(":id",$id);
        $stmt->bindValue(":status",$status);
        $stmt->execute();
    }

    /**
     * @param $name
     * @param $status
     */
    public function update($name, $status)
    {
        $stmt = $this->db->prepare("update notification set updated_at = :status where id = :id");
        $stmt->bindValue(":id",$name);
        $stmt->bindValue(":status",$status);
        $stmt->execute();

    }
}
