<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Patent;
use tdt4237\webapp\models\PatentCollection;

class PatentRepository
{

    /**
     * @var PDO
     */
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public static function create($id, $company, $title, $description, $date, $file)
    {
        $patent = new Patent;
        
        return $patent
            ->setPatentId($id)
            ->setCompany($company)
            ->setTitle($title)
            ->setDescription($description)
            ->setDate($date)
            ->setFile($file);
    }

    public function find($patentId)
    {
        $sql  = "SELECT * FROM patent WHERE patentId = $patentId";
        $result = $this->db->query($sql);
        $row = $result->fetch();

        if($row === false) {
            return false;
        }


        return $this->makeFromRow($row);
    }

    public function all()
    {
        $sql   = "SELECT * FROM patent";
        $results = $this->db->query($sql);

        if($results === false) {
            return [];
            throw new \Exception('PDO error in patent all()');
        }

        $fetch = $results->fetchAll();
        if(count($fetch) == 0) {
            return false;
        }

        return new PatentCollection(
            array_map([$this, 'makeFromRow'], $fetch)
        );
    }

    public function makeFromRow($row)
    {
        return static::create(
            $row['patentId'],
            $row['company'],
            $row['title'],
            $row['file'],
            $row['description'],
            $row['date']
        );
    }

    public function deleteByPatentid($patentId)
    {
        return $this->db->exec(
            sprintf("DELETE FROM patent WHERE patentid='%s';", $patentId));
    }


    public function save(Patent $patent)
    {
        $title          = $patent->getTitle();
        $company        = $patent->getCompany();
        $description    = $patent->getDescription();
        $date           = $patent->getDate();

        if ($patent->getPatentId() === null) {
            $query = "INSERT INTO patent (company, date, title, description) "
                . "VALUES ('$company', '$date', '$title', '$description')";
        }

        $this->db->exec($query);
        return $this->db->lastInsertId();
    }
}
