<?php
declare(strict_types=1);

namespace ExchangeShop\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use ExchangeShop\ExchangeShop;
use pocketmine\permission\DefaultPermissions;

class ShopCommand extends Command
{

  protected $plugin;
  private $chat;

  public function __construct(ExchangeShop $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('교환상점', '교환상점 명령어 입니다.', '/교환상점');
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    $name = $sender->getName ();
    if( ! isset($args[0] )){
      $sender->sendMessage ($this->plugin->tag());
      $sender->sendMessage ($this->plugin->tag()."/교환상점 열기 ( 교환상점이름 ) < 교환상점을 오픈합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/교환상점 목록 < 교환상점 목록을 확인합니다. >");
      return true;
    }
    switch ($args [0]) {
      case "열기" :
      if (isset($args[1])) {
        if (!isset($this->plugin->shopdb [$args[1]])){
          $sender->sendMessage ($this->plugin->tag()."해당 이름으로 교환상점이 없습니다.");
          return true;
        }
        if (! isset ( $this->chat [$name] )) {
          $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
          $this->plugin->save ();
          $this->plugin->PlayerShopOpen($sender);
          $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
          return true;
        }
        if (date("YmdHis") - $this->chat [$name] < 3) {
          $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
          return true;
        } else {
          $this->plugin->pldb [strtolower($name)] ["ShopName"] = $args[1];
          $this->plugin->save ();
          $this->plugin->PlayerShopOpen($sender);
          $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
          return true;
        }
      } else {
        $sender->sendMessage ($this->plugin->tag()."/교환상점 열기 ( 교환상점이름 ) < 교환상점을 오픈합니다. >");
        return true;
      }
      break;
      case "목록" :
      $sender->sendMessage ($this->plugin->tag());
      $sender->sendMessage ("§r§7교환상점 목록 -");
      foreach($this->plugin->shopdb as $shopname => $v){
        $sender->sendMessage ("§r§7- " . $shopname);
      }
      break;
    }
  }

}
