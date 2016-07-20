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

    function update(){
        $update = $this->solr->createUpdate();

        $model = new Model();
        $data = $model->db->getRows('st','*','1=1');
        foreach($data as $k => $v){
            $doc = $update->createDocument();
            $doc->code = $v['code'];
            $doc->name = $v['name'];
            $doc->flag = $v['flag'];
            $doc->create_time = $v['create_time'];
            $doc_list[] = $doc;
        }

        $update->addDocuments($doc_list);
        $update->addCommit();
        $result = $this->solr->update($update);
        return $result->getStatus();
    }

    function query($condition, $sort){

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
