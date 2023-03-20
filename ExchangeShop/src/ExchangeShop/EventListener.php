<?php
declare(strict_types=1);

namespace ExchangeShop;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\player\Player;
use pocketmine\Server;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\tile\Chest;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldManager;

use MoneyManager\MoneyManager;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\ContainerClosePacket;

use LifeInventoryLib\InventoryLib\InvLibManager;
use LifeInventoryLib\InventoryLib\LibInvType;
use LifeInventoryLib\InventoryLib\InvLibAction;
use LifeInventoryLib\InventoryLib\SimpleInventory;
use LifeInventoryLib\InventoryLib\LibInventory;

use pocketmine\permission\DefaultPermissions;

class EventListener implements Listener
{
  
  protected $plugin;
  private $chat;
  
  public function __construct(ExchangeShop $plugin)
  {
    $this->plugin = $plugin;
  }
  
  public function OnJoin (PlayerJoinEvent $event): void
  {
    $player = $event->getPlayer ();
    $name = $player->getName ();
    if (!isset($this->plugin->pldb [strtolower($name)])){
      $this->plugin->pldb [strtolower($name)] ["ShopName"] = "없음";
      $this->plugin->pldb [strtolower($name)] ["Slot"] = "없음";
      $this->plugin->save ();
    }
  }
  
  public function onTransaction(InventoryTransactionEvent $event)
  {
    $transaction = $event->getTransaction();
    $player = $transaction->getSource ();
    $name = $player->getName ();
    foreach($transaction->getActions() as $action){
      if($action instanceof SlotChangeAction){
        $inv = $action->getInventory();
        if($inv instanceof LibInventory){
          if ($inv->getTitle() == "[ 교환상점 ] | 보상세팅"){
            $slot = (int)$action->getSlot ();
            $item = $inv->getItem ($slot);
            $id = $item->getId ();
            $damage = $item->getMeta ();
            $itemname = $item->getCustomName ();
            $nbt = $item->jsonSerialize ();
            if ( $id == 63 ) {
              $event->cancel ();
              return true;
            }
            if ( $itemname == "설정완료" ) {
              $event->cancel ();
              $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
              $i = 0;
              while ($i <= 44){
                $item = $inv->getItem($i);
                if ($item->getId() != 0){
                  if (isset ($this->plugin->shopdb [$shopname] [$i."번"])){
                    unset ($this->plugin->shopdb [$shopname] [$i."번"]);
                    $this->plugin->save ();
                  }
                  $this->plugin->shopdb [$shopname] [$i."번"] ["nbt"] = $item->jsonSerialize();
                  $this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] = [];
                  $this->plugin->save ();
                } else {
                  if (isset($this->plugin->shopdb [$shopname] [$i."번"])){
                    unset ($this->plugin->shopdb [$shopname] [$i."번"]);
                    $this->plugin->save ();
                  }
                }
                ++$i;
              }
              $player->sendMessage ($this->plugin->tag() . "아이템 설정이 완료되었습니다.");
              $inv->onClose($player);
              return true;
            }
          }
          if ($inv->getTitle() == "[ 교환상점 ] | 교환상점재료세팅"){
            $slot = $action->getSlot ();
            $item = $inv->getItem ($slot);
            $id = $item->getId ();
            $damage = $item->getMeta ();
            $itemname = $item->getCustomName ();
            $nbt = $item->jsonSerialize ();
            if ( $id != 63 ) {
              $event->cancel ();
              $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
              $this->plugin->pldb [strtolower($name)] ["Slot"] = $slot;
              $this->plugin->save ();
              $inv->onClose($player);
              $this->plugin->ShopGiveSetGUI($player);
              return true;
            }
          }
          
          if ($inv->getTitle() == "[ 교환상점 ] | 교환재료"){
            $slot = $action->getSlot ();
            $item = $inv->getItem ($slot);
            $id = $item->getId ();
            $damage = $item->getMeta ();
            $itemname = $item->getCustomName ();
            $nbt = $item->jsonSerialize ();
            if ( $id == 63 ) {
              $event->cancel ();
              return true;
            }
            if ( $itemname == "설정완료" ) {
              $event->cancel ();
              $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
              $slot = $this->plugin->pldb [strtolower($name)] ["Slot"];
              $i = 0;
              while ($i <= 44){
                $item = $inv->getItem($i);
                if ($item->getId() != 0){
                  if (isset ($this->plugin->shopdb [$shopname] [$slot."번"] ["교환재료"] [$i."번"])){
                    unset ($this->plugin->shopdb [$shopname] [$slot."번"] ["교환재료"] [$i."번"]);
                    $this->plugin->save ();
                  }
                  $this->plugin->shopdb [$shopname] [$slot."번"] ["교환재료"] [$i."번"] = $item->jsonSerialize();
                  $this->plugin->save ();
                } else {
                  if (isset($this->plugin->shopdb [$shopname] [$slot."번"] ["교환재료"] [$i."번"])){
                    unset ($this->plugin->shopdb [$shopname] [$slot."번"] ["교환재료"] [$i."번"]);
                    $this->plugin->save ();
                  }
                }
                ++$i;
              }
              $player->sendMessage ($this->plugin->tag() . "아이템 설정이 완료되었습니다.");
              $inv->onClose($player);
              return true;
            }
            $event->cancel ();
          }
          
          $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
          if ($inv->getTitle() == "[ 교환상점 ] | {$shopname}"){
            $slot = $action->getSlot ();
            $item = $inv->getItem ($slot);
            $id = $item->getId ();
            $damage = $item->getMeta ();
            $itemname = $item->getCustomName ();
            $nbt = $item->jsonSerialize ();
            if ( $itemname == "나가기" ) {
              $event->cancel ();
              $player->sendMessage ($this->plugin->tag() . "상점에서 나왔습니다.");
              $inv->onClose($player);
              return true;
            }
            $event->cancel ();
            $inv->onClose($player);
            $this->plugin->pldb [strtolower($name)] ["Slot"] = $slot;
            $this->plugin->save ();
            $this->plugin->PlayerCheck ($player);
            return true;
          }
          
          $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
          if ($inv->getTitle() == "[ 교환상점 ] | {$shopname} 진행"){
            $slot = $action->getSlot ();
            $item = $inv->getItem ($slot);
            $id = $item->getId ();
            $damage = $item->getMeta ();
            $itemname = $item->getCustomName ();
            $nbt = $item->jsonSerialize ();
            if ( $itemname == "나가기" ) {
              $event->cancel ();
              $player->sendMessage ($this->plugin->tag() . "교환상점에서 나왔습니다.");
              $inv->onClose($player);
              return true;
            }
            if ( $itemname == "교환진행하기" ) {
              $event->cancel ();
              $inv->onClose($player);
              $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
              $i = $this->plugin->pldb [strtolower($name)] ["Slot"];
              foreach($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] as $count => $v){
                if(isset($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] [$count])){
                  $item = Item::jsonDeserialize($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] [$count]);
                  if ($player->getInventory ()->contains ( $item )){
                  } else {
                    $player->sendMessage ($this->plugin->tag() . "교환재료가 부족해서 진행하지 못합니다.");
                    return true;
                  }
                }
              }
              $shopname = $this->plugin->pldb [strtolower($name)] ["ShopName"];
              $i = $this->plugin->pldb [strtolower($name)] ["Slot"];
              foreach($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] as $count => $v){
                if(isset($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] [$count])){
                  $item = Item::jsonDeserialize($this->plugin->shopdb [$shopname] [$i."번"] ["교환재료"] [$count]);
                  $player->getInventory ()->removeItem ( $item );
                }
              }
              $nbtitem = Item::jsonDeserialize($this->plugin->shopdb [$shopname] [$i."번"] ["nbt"]);
              $player->getInventory ()->addItem ( $nbtitem );
              $player->sendMessage ($this->plugin->tag() . "정상적으로 물품을 교환했습니다.");
              return true;
            }
            $event->cancel ();
          }
        }
      }
    }
  }
  
}
