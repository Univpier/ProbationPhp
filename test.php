<?php
class Database
{
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
            $this->sql = "select * from test as t 
                      join responsible_test as rt on t.id = rt.test_id 
                      join responsible as r on r.responsibleId = rt.responsible_id ;";
            $this->query = $this->pdo->prepare($this->sql);
            $this->query->execute();
            $this->arr = $this->query->fetchAll(PDO::FETCH_ASSOC);
        return $this->arr;
    }

    function insertNewResponsibleInTable($arr) //Добавление ответственных
    {
        try {
            $this->pdo->beginTransaction();
            foreach ($arr as $value) {
                //либо camelCase либо sanke_case
                $responsible_names = $value['responsible_name'];
                $test_id = $value['divisionId'];
                $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
                //pdo->lastInsertId()
                $this->sql = "INSERT INTO responsible (responsible_name) VALUES (:responsible_name); ";
                $this->query = $this->pdo->prepare($this->sql);
                $this->query->bindParam(':responsible_name', $responsible_names);
                $this->query->execute();
                $lastInsert_id = $this->pdo->lastInsertId();
                $this->insertRelationResponsibleTest($lastInsert_id, $test_id);
            }
            $this->pdo->commit();
        }catch (\Exception $e) {
            $this->pdo->rollBack();
            throw Exception($e);
        }

    }
    function insertRelationResponsibleTest($last_insert_id,$test_id)
   {
       try {
           $this->pdo->beginTransaction();
           $this->sql = "INSERT INTO responsible_test (responsible_id,test_id) VALUES (:responsible_name,:tests_id);";
           $this->query = $this->pdo->prepare($this->sql);
           $this->query->bindParam(':responsible_name', $last_insert_id);
           $this->query->bindParam(':tests_id', $test_id);
           $this->query->execute();
           $this->pdo->commit();
       } catch (\Exception $e) {
           $this->pdo->rollBack();
           throw Exception($e);
       }
   }
   function deleteRelationResponsibleTest($arr){
        foreach($arr as $value){
            try{
                $this->pdo->beginTransaction();
                $test_id = $value['divisionId'];
                $this->sql ="DELETE FROM responsible_test WHERE :tests_id";
                $this->query = $this->pdo->prepare($this->sql);
                $this->query->bindParam(':tests_id',$test_id);
                $this->query->execute();
                $this->pdo->commit();
            }catch (\Exception $e) {
                $this->pdo->rollBack();
            throw Exception($e);
            }

        }
        $this->insertNewResponsibleInTable($arr);
    }

    public function updatePlainTreeOneNode($another_name,$parent_id,$id)// обновление узла
    {
        try{
            $this->pdo->beginTransaction();
            $this->sql ="UPDATE test SET name = ?, parentId = ? where id= ?";
            $this->query = $this->pdo->prepare($this->sql);
            $this->query->execute([$another_name,$parent_id,$id]);
            $this->pdo->commit();
        }catch (\Exception $e) {
            $this->pdo->rollBack();
            throw Exception($e);
        }

    }

    public function updatePlainTreeOneNode1($array_update)// обновление узла
    {
        foreach($array_update as $value){
            $responsible_name = $value['responsible_name'];
            $responsible_id = $value['divisionId'];
            try{
                $this->pdo->beginTransaction();
                $this->sql ="UPDATE responsible SET responsible_name = ? where responsibleId= ? ;";
                $this->query = $this->pdo->prepare($this->sql);
                $this->query->execute([$responsible_name,$responsible_id]);
                $this->pdo->commit();
            }catch (\Exception $e) {
                $this->pdo->rollBack();
                throw Exception($e);
            }
        }

    }
    private function getNodes(int $parent_id): array // return отфилтрованного по parentId массива
    {
        $array = [];
        foreach ($this->setupPlainTree() as $value) {
            if($parent_id !== (int)$value['parentId']) continue;
            $array[] = $value;
        }
        return $array;
    }

    private function getOneNode(int $parent_id): array//return отфилтрованного по id массива
    {
        foreach ($this->setupPlainTree() as $value) {
            if($parent_id !== (int)$value['parentId']) continue;
            $array[] = $value;
        }
        return $array;
    }

    public function createTree($parent_id = 0,$getOneNode = false )// возвращает вложенное дерево(При значении $getOneNode = true выводит вложенность узла по id узла)
    {
        $array = [];
        if($getOneNode == false){
            $nodes = $this->getNodes($parent_id);
        }else{
            $nodes = $this->getOneNode($parent_id);
        }

        foreach ($nodes as $value)
        {
            $array[] = [
                'responsible' => $value['responsible_name'],
                'id' => $value['id'],
                'name' => $value['name'],
                'parentId' => $value['parentId'],
                'children' => $this->createTree($value['id'])
            ];
        }

        return $array;

    }

     public function deletePlainTreeOneNodeWithChildren($parentId =0 ,$getOneNode = false ) // удаление узла с вложенностью ( При значении $getOneNode = true удаляет вложенность узла по id узла)
     {
           $array_for_delete = $this->createTree(6,true);
//         if($getOneNode == false){
//             $arrayForDelete = $this->getNodes($parentId);
//         }else{
//             $arrayForDelete = $this->getOneNode($parentId);
//         }
         foreach ($array_for_delete as $value){
             try{
                 $this->pdo->beginTransaction();
                 $this->sql = "delete from test where id = ? ;";
                 $this->query = $this->pdo->prepare($this->sql);
                 $this->deletePlainTreeOneNodeWithChildren($value['id']);
                 $this->pdo->commit();
             }catch (\Exception $e) {
                 $this->pdo->rollBack();
                 throw Exception($e);
             }

         }

    }

}

$database1 = new Database('localhost',  'probation', 'root','');

$array = [
    ['divisionId' => 1, 'responsible_name' => 'Responsible 1'],
    ['divisionId' => 2, 'responsible_name' => 'responsible 2'],
    ['divisionId' => 3, 'responsible_name' => 'responsible 3'],
    ['divisionId' => 4, 'responsible_name' => 'responsible 4'],
    ['divisionId' => 5, 'responsible_name' => 'responsible 5'],
    ['divisionId' => 6, 'responsible_name' => 'responsible 6'],
    ['divisionId' => 7, 'responsible_name' => 'responsible 7'],
    ['divisionId' => 8, 'responsible_name' => 'responsible 8'],
    ['divisionId' => 9, 'responsible_name' => 'responsible 9'],
    ['divisionId' => 10, 'responsible_name' => 'responsible 10'],
    ['divisionId' => 11, 'responsible_name' => 'responsible 11']

];
$arrayUpdate = [
    ['divisionId' => 1, 'responsible_name' => 'responsible 2'],
    ['divisionId' => 2, 'responsible_name' => 'responsible 1'],
    ['divisionId' => 3, 'responsible_name' => 'responsible 3'],
    ['divisionId' => 4, 'responsible_name' => 'responsible 4'],
    ['divisionId' => 5, 'responsible_name' => 'responsible 5'],
    ['divisionId' => 6, 'responsible_name' => 'responsible 6'],
    ['divisionId' => 7, 'responsible_name' => 'responsible 7'],
    ['divisionId' => 8, 'responsible_name' => 'responsible 8'],
    ['divisionId' => 9, 'responsible_name' => 'responsible 9'],
    ['divisionId' => 10, 'responsible_name' => 'responsible 10'],
    ['divisionId' => 11, 'responsible_name' => 'responsible 11']
];

//вывод дерева
//echo json_encode($database1->createTree(0));

// вывод дерева одного узла
//echo json_encode($database1->createTree(6,true));

//обновление узла
//$database1->updatePlainTreeOneNode('NewprogramLanguage',0,1);

//удаление узла с вложенностью
//$database1->deletePlainTreeOneNodeWithChildren(6,true);

//Редактирование ответственных(передача актуального списка)
//$database1->updatePlainTreeOneNode1($arrayUpdate);

//Добавление ответственных
$database1->deleteRelationResponsibleTest($array);

//$database1->insertNewResponsibleInTable($array);
//$database1->insertRelationResponsibleTest($array);
//$database1->insertResponsible('3sfdsdfsdfsdf');
