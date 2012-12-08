<?php
/*  ShowExtgroup 
 *  Plugin FOR Discuz! X1 & X1.5 & X2
 *	Copyright (c) 2009-2010 WWW.NWDS.CN | NDS.西域数码工作室
 *	$Id: nds_showgroups.class.php V1.5 UPDATE 20100927 20100909 SINGCEE $
 */

if(!defined('IN_DISCUZ')) {
     exit('Access Denied');
}

class plugin_nds_showgroups {
     function plugin_nds_showgroups() {
          global $_G;
          $this->showgroupstitle = $_G['cache']['plugin']['nds_showgroups']['showgroupstitle'];
          $this->showgroupstitlesite = $_G['cache']['plugin']['nds_showgroups']['showgroupstitlesite'];
          $this->showgroupstitlesitecolor = $_G['cache']['plugin']['nds_showgroups']['showgroupstitlesitecolor'];
          $this->showgroupssite = $_G['cache']['plugin']['nds_showgroups']['showgroupssite'];
          $this->showgroupcolor = $_G['cache']['plugin']['nds_showgroups']['showgroupcolor'];
          $this->showgroupsmax = $_G['cache']['plugin']['nds_showgroups']['showgroupsmax'];
          $this->showgroupsorder = $_G['cache']['plugin']['nds_showgroups']['showgroupsorder'];
          $this->showgroupsban = $_G['cache']['plugin']['nds_showgroups']['showgroupsban'];
     }
}
class plugin_nds_showgroups_forum extends plugin_nds_showgroups {
     function nds_showgroups_go() {
          $ndsreturn = array();
          global $_G,$postlist;
          if(empty($postlist) || !is_array($postlist)) return $ndsreturn;
          $postuid = '';
          $groupsorder = '';
           switch ($this->showgroupsorder) {
 	            case 1:           
                  $groupsorder = 'f.fid';      
                  break; 
                case 2:           
                  $groupsorder = 'f.posts + f.threads DESC';      
                  break;
                case 3:           
                  $groupsorder = 'g.threads+g.replies DESC';      
                  break;
                case 4:           
                  $groupsorder = 'f.`level`DESC ,g.level DESC';      
                  break;
                case 5:           
                  $groupsorder = 'g.joindateline';      
                  break;
                default:
                 $groupsorder = 'f.fid'; 	
           } 
           if (!trim($this->showgroupsban)) {
           	 $groupsban = '';  
           }else {
            $groupsban = ' AND f.fid not in('.str_replace("，",",",$this->showgroupsban).')';
           }
          $groupsarrs = array();
          $groupsarr = array();
          $this->showgroupstitle = '<font color="'.$this->showgroupstitlesitecolor.'">'.$this->showgroupstitle.'</font>';
          foreach ($postlist as $pid =>$post) {
               $postuid = $post['authorid'];
               $groupsarrs = DB::query("SELECT f.fid, f.name FROM ".DB::table('forum_groupuser')." g 
               LEFT JOIN ".DB::table('forum_forum')." f ON f.fid = g.fid WHERE g.uid = '$postuid' ".$groupsban." ORDER BY ".$groupsorder." LIMIT 0, $this->showgroupsmax");
               if (!$groupsarrs) return $ndsreturn;
               $groupstitle ='';
               $groupstitles = '';
             while($groupsarr = DB::fetch($groupsarrs)) {
               	 if ($this->showgroupstitlesite == 1 ) {
                  $groupstitle = '<p><em>'.$this->showgroupstitle.':&nbsp;<a href="forum.php?mod=group&fid='.$groupsarr[fid].'"><font color="'.$this->showgroupcolor.'">'.$groupsarr[name].'</font></a></em></p>';
               	  }else{
               	   $groupstitle = '<p><em><a href="forum.php?mod=group&fid='.$groupsarr[fid].'"><font color="'.$this->showgroupcolor.'">'.$groupsarr[name].'</font></a>&nbsp;'.$this->showgroupstitle.'</em></p>';
                 }
                 $groupstitles = $groupstitles.$groupstitle ;
               }
               $ndsreturn[] = $groupstitles;   
               }
              return $ndsreturn;   
          }

     function viewthread_sidetop_output() {
          if ($this->showgroupssite <> '1' ) return array();
          return  $this->nds_showgroups_go();
     }

     function viewthread_sidebottom_output() {
          if ($this->showgroupssite <> '2' ) return array();
          return  $this->nds_showgroups_go();
     }

}         

class plugin_nds_showgroups_group extends plugin_nds_showgroups_forum {
	
     function viewthread_sidetop_output() {
          if ($this->showgroupssite <> '1' ) return  array();
          return  $this->nds_showgroups_go();
     }

     function viewthread_sidebottom_output() {
          if ($this->showgroupssite <> '2' ) return  array();
          return  $this->nds_showgroups_go();
     }	
}

?>