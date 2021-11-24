<?php

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
     function setupPlainTree() // получает массив таблицы из базы данных
    {
        $this->sql = "select * from test as t join responsible_test as rt on t.id = rt.test_id join responsible as r on r.responsibleId = rt.responsible_id ;";
        $this->query = $this->pdo->prepare($this->sql);
        $this->query->execute();
        $this->arr = $this->query->fetchAll(PDO::FETCH_ASSOC);
//        $this->plainTree = $this->arr;
        return $this->arr;
    }

    function updatePlainTreeOneNode($anotherName,$parentId,$id)// обновление узла
    {
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
//    function getResponsibleForUpdate(...$arr){
//        foreach($arr as $value){
//
//        }
//        updatePlainTreeOneNode1();
//    }

    function updatePlainTreeOneNode1(...$arr)// обновление узла
    {
        $lenArr =count($arr);
        for($i = 0; $i < $lenArr; $i+=3){
            $responsibleIdOld = $arr[i];
            $responsibleId = $arr[i+1];
            $responsible_name = $arr[i+2];
            try{
                $this->pdo->beginTransaction();
                $this->sql ="UPDATE responsible_test SET responsibleId = ?, responsible_name = ? where responsibleIdOld= ?";
                $this->query = $this->pdo->prepare($this->sql);
                $this->query->execute([$responsibleIdOld,$responsibleId,$responsible_name]);
                $this->pdo->commit();
            }catch (\Exception $e) {
                $this->pdo->rollBack();
//            throw new Exception($e);
            }
        }




    }
    function getNodes(int $parentId): array // return отфилтрованного по parentId массива
    {
        $array = [];
        foreach ($this->setupPlainTree() as $value) {
            if ($parentId === (int)$value['parentId']) {
                $array[] = $value;
            }
        }
        return $array;
    }
    function getOneNode(int $parentId): array//return отфилтрованного по id массива
    {
        foreach ($this->setupPlainTree() as $value) {
            if ($parentId === (int)$value['id']) {
                $array[] = $value;
            }
        }
        return $array;
    }


    function createTree($parentId = 0,$getOneNode = false )// возвращает вложенное дерево(При значении $getOneNode = true выводит вложенность узла по id узла)
    {
        $array = [];
        if($getOneNode == false){
            $nodes = $this->getNodes($parentId);
        }else{
            $nodes = $this->getOneNode($parentId);
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
     function deletePlainTreeOneNodeWithChildren($parentId =0 ,$getOneNode = false ) // удаление узла с вложенностью ( При значении $getOneNode = true удаляет вложенность узла по id узла)
     {
//        $array_for_delete = $this->createTree(6,true);
         if($getOneNode == false){
             $array_for_delete = $this->getNodes($parentId);
         }else{
             $array_for_delete = $this->getOneNode($parentId);
         }
         foreach ($array_for_delete as $value){
             $this->sql = "delete from test where id = ? ;";
             $value_id = $value['id'];
             $this->query = $this->pdo->prepare($this->sql);
             $this->query->execute([$value_id]);
             $this->deletePlainTreeOneNodeWithChildren($value['id']);
         }

    }

}

$database1 = new Database('localhost',  'probation', 'root','');



 //вывод дерева
//echo json_encode($database1->createTree(0));

// вывод дерева одного узла
//echo json_encode($database1->createTree(6,true));

//обновление узла
//$database1->updatePlainTreeOneNode('NewprogramLanguage',0,1);

//удаление узла с вложенностью
//$database1->deletePlainTreeOneNodeWithChildren(6,true);

$database1->updatePlainTreeOneNode1();