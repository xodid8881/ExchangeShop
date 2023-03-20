<?php
declare(strict_types=1);

namespace ExchangeShop\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use ExchangeShop\ExchangeShop;
use pocketmine\permission\DefaultPermissions;

class ShopSettingCommand extends Command
{

  protected $plugin;

  public function __construct(ExchangeShop $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('교환상점설정', '교환상점을 관리하는 명령어 합니다.', '/교환상점설정');
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    $name = $sender->getName ();
    if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
      $sender->sendMessage($this->plugin->tag()."권한이 없습니다.");
      return true;
    }
    if( ! isset($args[0] )){
      $sender->sendMessage ($this->plugin->tag());
      $sender->sendMessage ($this->plugin->tag()."/교환상점설정 생성 ( 교환상점이름 ) < 교환상점을 생성합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/교환상점설정 보상세팅 ( 교환상점이름 ) < 교환이 완료된 후 받을 물품을 세팅합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/교환상점설정 교환재료세팅 ( 교환상점이름 ) < 교환에 사용될 물품을 세팅합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/교환상점설정 삭제 ( 교환상점이름 ) < 교환상점을 삭제합니다. >");
      return true;
    }
    switch ($args [0]) {
      case "생성" :
      if (isset($args[1])) {
        if (isset($this->plugin->shopdb [$args[1]])){
          $sender->sendMessage ($this->plugin->tag()."이미 해당 이름으로 교환상점이 만들어져 있습니다.");
        }
        $this->plugin->shopdb [$args[1]] = [];
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag(). $args[1] ."교환상점이 생성되었습니다.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."/교환상점설정 생성 ( 교환상점이름 ) < 교환상점을 생성합니다. >");
        return true;
      }
      break;
      case "교환재료세팅" :
      if (isset($args[1])) {
        if (isset($this->plugin->shopdb [$args[1]])){
          if (! isset ( $this->chat [$name] )) {
            $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
            $this->plugin->save ();
            $this->plugin->ShopGiveSettingGUI($sender);
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
          if (date("YmdHis") - $this->chat [$name] < 3) {
            $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
            return true;
          } else {
            $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
            $this->plugin->save ();
            $this->plugin->ShopGiveSettingGUI($sender);
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
        } else {
          $sender->sendMessage ($this->plugin->tag(). $args[1] . "라는 교환상점이 존재하지 않습니다.");
          return true;
        }
      } else {
        $sender->sendMessage ($this->plugin->tag()."/교환상점설정 교환재료세팅 ( 교환상점이름 ) < 교환에 사용될 물품을 세팅합니다. >");
        return true;
      }
      break;
      case "보상세팅" :
      if (isset($args[1])) {
        if (isset($this->plugin->shopdb [$args[1]])){
          if (! isset ( $this->chat [$name] )) {
            $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
            $this->plugin->save ();
            $this->plugin->ShopAddSettingGUI($sender);
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
          if (date("YmdHis") - $this->chat [$name] < 3) {
            $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
            return true;
          } else {
            $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
            $this->plugin->save ();
            $this->plugin->ShopAddSettingGUI($sender);
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
        } else {
          $sender->sendMessage ($this->plugin->tag(). $args[1] . "라는 교환상점이 존재하지 않습니다.");
          return true;
        }
      } else {
        $sender->sendMessage ($this->plugin->tag()."/교환상점설정 보상세팅 ( 교환상점이름 ) < 교환이 완료된 후 받을 물품을 세팅합니다. >");
        return true;
      }
      break;
      case "삭제" :
      if (isset($args[1])) {
        if (!isset($this->plugin->shopdb [$args[1]])){
          $sender->sendMessage ($this->plugin->tag(). $args[1] . "라는 교환상점이 존재하지 않습니다.");
        }
        unset($this->plugin->shopdb [$args[1]]);
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag(). $args[1] ."교환상점이 삭제되었습니다.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."/교환상점설정 삭제 ( 교환상점이름 ) < 교환상점을 삭제합니다. >");
        return true;
      }
      break;
    }
  }

}
