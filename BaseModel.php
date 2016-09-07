<?php
/**
 * Created by PhpStorm.
 * User: chen
 * Date: 2016/6/3
 * Time: 16:58
 */
class BaseModel{

    private static $client = NULL;
    public $solr = NULL;

    public function __construct($config){
        if(self::$client === NULL){
            self::$client = new Solarium\Client($config);
        }
        $this->solr = self::$client;
    }

    private function __clone() {
        return $this->solr;
    }

    function getVersion(){
         return Solarium\Client::VERSION;
    }

    function ping(){
        $ping = $this->solr->createPing();
        try {
            $this->solr->ping($ping);
            return true;
        } catch (Solarium\Exception $e) {
            return false;
        }

    }

    function updateAll(){
        $this->deleteByQuery('id:*');//删除现有
        $update = $this->solr->createUpdate();

        $model = new Model();
        $data = $model->db->getRows('s_user','*','1=1');
        if(empty($data)) return 0;
        $doc_list = [];
        foreach($data as $k => $v){
            $doc = $update->createDocument();
            foreach($v as $key => $value)
                $doc->$key = $value;

            $doc_list[] = $doc;
        }

        $update->addDocuments($doc_list);
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result->getStatus();
    }

    function updateById($id){
        $update = $this->solr->createUpdate();

        $model = new Model();
        $data = $model->db->getRow('s_user','*','id='.$id);
        if(empty($data)) return 0;

        $doc = $update->createDocument();
        foreach($data as $key => $value){
            $doc->$key = $value;
        }

        $update->addDocument($doc);
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result->getStatus();
    }

    function query($condition = 'name:*', $sort_key = 'update_time', $sort_asc = true){
        $query = $this->solr->createSelect();
        $search_time = date('YmdHis', time()-90*24*3600);
        $condition .= ' AND create_time:['.$search_time.' TO * ]';
        $query->setQuery($condition);
        $query->setFields(['id']);
        $query->addSort($sort_key, $sort_asc?$query::SORT_ASC:$query::SORT_DESC);
        $result = $this->solr->select($query);
        $numFound = $result->getNumFound();
        $id_list = [];
        if($numFound > 0 ){
            foreach($result as $rs){
                $id_list[] = $rs->id;
            }
        }
        return ['numFound'=>$numFound, 'id_list'=>$id_list];
        //array(2) { ["numFound"]=> int(2) ["id_list"]=> array(2) { [0]=> string(1) "1" [1]=> string(1) "2" } }
    }

    function deleteById($id){
        $update = $this->solr->createUpdate();
        $update->addDeleteById($id);
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result->getStatus();
    }

    function deleteByQuery($q){
        $update = $this->solr->createUpdate();
        $update->addDeleteQuery($q);
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result->getStatus();
    }
}
