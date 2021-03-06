<?php namespace components\sdk; if(!defined('TX')) die('No direct access.');

//Make sure we have the things we need for this class.
tx('Component')->check('update');
tx('Component')->load('update', 'classes\\BaseDBUpdates', false);

class DBUpdates extends \components\update\classes\BaseDBUpdates
{
  
  protected
    $component = 'sdk',
    $updates = array(
      '1.0' => '0.0.2-alpha',
      '0.0.2-alpha' => '0.0.3-alpha' //No DB changes.
    );
  
  public function update_to_0_0_2_alpha($current_version, $forced)
  {
    
    //Queue this because it would get it's changes undone in the DB otherwise.
    $this->queue(array(
      'component' => 'update',
      'min_version' => '0.2.0-beta'
      ), function($version){
        
        //Rename the package.
        mk('Component')->helpers('update')->call('rename_component_package', array(
          'component' => 'sdk',
          'old_title' => 'Tuxion CMS - SDK'
        ));
        
      }
    ); //END - Queue
    
  }
  
  public function install_1_0($dummydata, $forced)
  {
    
    if($forced === true){
      tx('Sql')->query('DROP TABLE IF EXISTS `#__sdk__translation__missing_files`');
      tx('Sql')->query('DROP TABLE IF EXISTS `#__sdk__translation__missing_phrases`');
    }
    
    tx('Sql')->query('
      CREATE TABLE `#__sdk__translation__missing_files` (
        `language_code` varchar(10) NOT NULL,
        `component` varchar(255) NULL,
        `component_version` varchar(255) NULL,
        `dt_last_registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`language_code`, `component`),
        KEY `dt_last_registered` (`dt_last_registered`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ');
    
    tx('Sql')->query('
      CREATE TABLE `#__sdk__translation__missing_phrases` (
        `language_code` varchar(10) NOT NULL,
        `phrase_hash` varchar(40) NOT NULL COMMENT \'SHA1\',
        `component` varchar(255) NULL,
        `component_version` varchar(255) NULL,
        `phrase` TEXT NOT NULL,
        `dt_last_registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`language_code`, `component`, `phrase_hash`),
        KEY `dt_last_registered` (`dt_last_registered`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8
    ');
    
    //Queue self-deployment with CMS component.
    $this->queue(array(
      'component' => 'cms',
      'min_version' => '1.2'
      ), function($version){
        
        //Ensures the mail component and mailing view.
        tx('Component')->helpers('cms')->_call('ensure_pagetypes', array(
          array(
            'name' => 'sdk',
            'title' => 'Tuxion CMS - SDK component'
          ),
          array(
            'sdk_dashboard' => true
          )
        ));
        
      }
    ); //END - Queue CMS 1.2+
    
  }
  
}

