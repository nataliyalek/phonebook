<?php

namespace App\Repositories;

abstract class CoreRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    abstract protected function getModelClass();

    protected function startCondition(){
        return clone $this->model;
    }

    public function getId($id){
        return $this->startCondition()->find($id);
    }

    public function  getRequestId($get=true, $id = 'id'){
        if ($get){
            $data = $_GET;
        }else{
            $data = $_POST;
        }
        $id = !empty($data[$id]) ? (int) $data[$id] : null;

        if(!$id){
            throw new \Exception('Проверить $id', 404);
        }

        return $id;
    }
}
