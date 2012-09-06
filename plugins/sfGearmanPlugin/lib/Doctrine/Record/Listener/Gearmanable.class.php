<?php

/**
 * Doctrine_Record_Listener_Gearmanable
 *
 * @uses      Doctrine_Template
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: Gearmanable.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
class Doctrine_Record_Listener_Gearmanable extends Doctrine_Record_Listener
{
  /**
   * Gearmanable template
   *
   * @var Doctrine_Template_Gearmanable  Defaults to null.
   */
  protected $_template = null;

  /**
   * __construct
   *
   * @param Doctrine_Template_Gearmanable $template
   */
  public function __construct(Doctrine_Template_Gearmanable $template)
  {
    $this->_template = $template;
  }

  /**
   * INSERT event
   *
   * @param Doctrine_Event $event
   *
   * @return void
   */
  public function postInsert(Doctrine_Event $event)
  {
    if (in_array('insert', $this->_template->getOption('events')))
    {
      $event->getInvoker()->taskBackground('triggerInsert');
    }
  }

  /**
   * UPDATE event
   *
   * @param Doctrine_Event $event
   *
   * @return void
   */
  public function postUpdate(Doctrine_Event $event)
  {
    if (in_array('update', $this->_template->getOption('events')))
    {
      $event->getInvoker()->taskBackground('triggerUpdate',
        array_keys($event->getInvoker()->getLastModified())
      );
    }
  }

  /**
   * DELETE event
   *
   * @param Doctrine_Event $event
   *
   * @return void
   */
  public function postDelete(Doctrine_Event $event)
  {
    if (in_array('delete', $this->_template->getOption('events')))
    {
      $event->getInvoker()->taskBackground('triggerDelete');
    }
  }
}

