<?php
class PluginNewsSitenews{
// <editor-fold defaultstate="collapsed" desc="Variables">
  public $settings;
  public $mysql;
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Construct">
  function __construct($buto) {
    if($buto){
      wfPlugin::includeonce('wf/yml');
      wfPlugin::includeonce('wf/mysql');
      wfPlugin::includeonce('wf/array');
      $this->mysql =new PluginWfMysql();
      $this->settings = wfPlugin::getPluginSettings('news/sitenews', true);
    }
  }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Widget">
  public function widget_flash(){
    $rs = $this->db_select();
    $element = $this->getElement('flash');
    $item = $this->getElement('flash_item');
    $items = array();
    foreach ($rs as $key => $value) {
      $row = new PluginWfArray($value);
      $item->setById('date', 'innerHTML', $row->get('date'));
      $item->setById('headline', 'innerHTML', $row->get('headline'));
      $item->setById('description', 'innerHTML', $this->formatText($row->get('description')));
      $item->setById('btn', 'attribute/data-id', $row->get('id'));
      $items[] = $item->get('item');
    }
    $element->setById('item', 'innerHTML', $items);
    wfDocument::renderElement($element->get());
  }
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Database">
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
    return new PluginWfArray($this->mysql->getStmtAsArrayOne());
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
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Methods">
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
    $txt = str_replace("\n", "<br>", $txt);
    return $txt;
  }
  public function form_render($form){
    $rs = $this->db_select_one(wfRequest::get('id'));
    $form->set('items/id/default', $rs->get('id'));
    $form->set('items/date/default', $rs->get('date'));
    $form->set('items/headline/default', $rs->get('headline'));
    $form->set('items/description/default', $rs->get('description'));
    $form->set('items/description_more/default', $rs->get('description_more'));
    return $form;
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
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Page">
  public function page_view(){
    $rs = $this->db_select_one(wfRequest::get('id'));
    $element = $this->getElement('view');
    $element->setById('btn_edit', 'attribute/data-id', $rs->get('id'));
    $element->setById('btn_delete', 'attribute/data-id', $rs->get('id'));
    $element->setById('date', 'innerHTML', $rs->get('date'));
    $element->setById('headline', 'innerHTML', $rs->get('headline'));
    $element->setById('description', 'innerHTML', $this->formatText($rs->get('description')));
    $element->setById('description_more', 'innerHTML', $this->formatText($rs->get('description_more')));
    wfDocument::renderElement($element->get());
  }
  public function page_form(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $form = $this->getForm('sitenews');
    $widget = wfDocument::createWidget('wf/form_v2', 'render', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function page_capture(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $form = $this->getForm('sitenews');
    $widget = wfDocument::createWidget('wf/form_v2', 'capture', $form->get());
    wfDocument::renderElement(array($widget));
  }
  public function page_delete(){
    if(!wfUser::hasRole('webadmin')){
      exit('.');
    }
    $this->db_delete(wfRequest::get('id'));
    exit('.');
  }
// </editor-fold>
}
