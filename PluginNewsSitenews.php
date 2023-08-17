<?php
class PluginNewsSitenews{
  public $settings;
  public $mysql;
  function __construct($buto) {
    if($buto){
      wfPlugin::includeonce('wf/yml');
      wfPlugin::includeonce('wf/mysql');
      wfPlugin::includeonce('wf/array');
      $this->mysql =new PluginWfMysql();
      $this->settings = wfPlugin::getPluginSettings('news/sitenews', true);
    }
  }
  public function widget_flash($data){
    $data = new PluginWfArray($data);
    if(!$data->get('data/headline')){
      $data->set('data/headline', 'News');
    }
    $rs = $this->db_select();
    $element = $this->getElement('flash');
    $element->setByTag($data->get('data'));
    $items = array();
    foreach ($rs as $key => $value) {
      $display='';
      if(!$data->get('data/show_all')){
        if($key>0){
          $display = 'none';
        }
      }
      /**
       * 
       */
      $row = new PluginWfArray($value);
      $row->set('display', $display);
      /**
       * 
       */
      $item = $this->getElement('flash_item');
      $item->setByTag($row->get());
      $items[] = $item->get('item');
    }
    /**
     * Add Show all item.
     */
    $item = $this->getElement('flash_item_show_all');
    $item->setByTag($data->get('data'), 'rs', true);
    $items[] = $item->get();
    /**
     * 
     */
    $element->setByTag(array('items' => $items));
    wfDocument::renderElement($element->get());
  }
  public function db_open(){
    $this->mysql->open($this->settings->get('data/mysql'));
  }
  private function db_select(){
    $sql = $this->getSql('select');
    $this->db_open();
    $this->mysql->execute($sql->get());
    return $this->mysql->getStmtAsArray();
  }
  private function db_select_one($id){
    $sql = $this->getSql('select_one');
    $sql->set('params/id/value', $id);
    $this->db_open();
    $this->mysql->execute($sql->get());
    $rs = new PluginWfArray($this->mysql->getStmtAsArrayOne());
    /**
     * 
     */
    wfPlugin::includeonce('readme/parser');
    $parser = new PluginReadmeParser();
    $rs->set('description_more_rm', $parser->parse_text($rs->get('description_more')));;
    /**
     * 
     */
    return $rs;
  }
  private function db_update($id){
    $sql = $this->getSql('update');
    $sql->set('params/id/value', $id);
    $sql->set('params/date/value', wfRequest::get('date'));
    $sql->set('params/headline/value', wfRequest::get('headline'));
    $sql->set('params/description/value', wfRequest::get('description'));
    $sql->set('params/description_more/value', wfRequest::get('description_more'));
    $this->db_open();
    $this->mysql->execute($sql->get());
    return null;
  }
  private function db_insert(){
    $id = wfCrypt::getUid();
    $sql = $this->getSql('insert');
    $sql->set('params/id/value', $id);
    $this->db_open();
    $this->mysql->execute($sql->get());
    return $id;
  }
  private function db_delete($id){
    $sql = $this->getSql('delete');
    $sql->set('params/id/value', $id);
    $this->db_open();
    $this->mysql->execute($sql->get());
    return null;
  }
  public function getElement($name){
    return new PluginWfYml(__DIR__."/element/$name.yml");
  }
  public function getForm($name){
    return new PluginWfYml(__DIR__."/form/$name.yml");
  }
  public function getSql($key){
    return new PluginWfYml(__DIR__.'/mysql/sql.yml', $key);
  }
  public function formatText($txt){
    $txt = wfPhpfunc::str_replace("\n", "<br>", $txt);
    return $txt;
  }
  public function form_render($form){
    $form = new PluginWfArray($form);
    $rs = $this->db_select_one(wfRequest::get('id'));
    $form->set('items/id/default', $rs->get('id'));
    $form->set('items/date/default', $rs->get('date'));
    $form->set('items/headline/default', $rs->get('headline'));
    $form->set('items/description/default', $rs->get('description'));
    $form->set('items/description_more/default', $rs->get('description_more'));
    return $form->get();
  }
  public function form_capture(){
    $id = null;
    if(!wfRequest::get('id')){
      /**
       * Create.
       */
      $id = $this->db_insert();
      $this->db_update($id);
      return array("location.reload();");
    }else{
      $id = wfRequest::get('id');
      $this->db_update($id);
      return array("$('#modal_sitenews_form').modal('hide');PluginWfAjax.update('modal_sitenews_body');");
    }
  }
  public function page_view(){
    $rs = $this->db_select_one(wfRequest::get('id'));
    $element = $this->getElement('view');
    $element->setByTag($rs->get());
    wfDocument::renderElement($element->get());
  }
  public function page_form(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $form = $this->getForm('sitenews');
    $widget = wfDocument::createWidget('form/form_v1', 'render', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function page_capture(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $form = $this->getForm('sitenews');
    $widget = wfDocument::createWidget('form/form_v1', 'capture', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function page_delete(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $this->db_delete(wfRequest::get('id'));
    exit('.');
  }
}
