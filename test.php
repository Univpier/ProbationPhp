<?php
//require_once vendor\phpoffice\phpspreadsheet\Spreadsheet;
//phpinfo();
//"phpoffice/phpspreadsheet": "^1.8"

class Database
{

    public $plainTree;
    public $arrTree;

    function __construct($host, $dbname, $username, $passwd)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname",$this->username,$this->passwd);
    }
     function setupPlainTree()
    {
        $this->sql = "select * from test;";
        $this->query = $this->pdo->prepare($this->sql);
        $this->query->execute();
        $this->arr = $this->query->fetchAll(PDO::FETCH_ASSOC);
//        $this->plainTree = $this->arr;
        return $this->arr;
    }
    function setupPlainTreeOneNode($id){
        $this->sql ="select * from test where id= ?";
        $this->query = $this->pdo->prepare($this->sql);
        $this->query->execute([$id]);
        $this->arr = $this->query->fetchAll(PDO::FETCH_ASSOC);
        $this->plainTree = $this->arr;
        echo json_encode($this->arr);
    }
    function updatePlainTreeOneNode($anotherName,$parentId,$id){
        try{
            $this->pdo->beginTransaction();
            $this->sql ="UPDATE test SET name = ?, parentId = ? where id= ?";
            $this->query = $this->pdo->prepare($this->sql);
            $this->query->execute([$anotherName,$parentId,$id]);
            $this->pdo->commit();
        }catch (\Exception $e) {
            $this->pdo->rollBack();
//            throw new Exception($e);
        }

    }

    function getNodes(int $parentId): array
    {
        $array = [];
        foreach ($this->setupPlainTree() as $value) {
            if ($parentId === (int)$value['parentId']) {
                $array[] = $value;
            }
        }
        return $array;
    }

    function createTree($parentId = 0)
    {
        $array = [];
        $nodes = $this->getNodes($parentId);
        foreach ($nodes as $value) {
            $array[] = [
                'id' => $value['id'],
                'name' => $value['name'],
                'parentId' => $value['parentId'],
                'children' => $this->createTree($value['id'])
            ];
        }

        return $array;

    }
    static public function exel(){

    }
}

//$database1 = new Database('localhost',  'probation', 'root','');
//$database1->setupPlainTree();
////$database1->updatePlainTreeOneNode('NewprogramLanguage',0,1);
////$database1->setupPlainTreeOneNode(1);
//echo json_encode($database1->createTree(0));
//$database1->setupPlainTreeOneNode(1);
