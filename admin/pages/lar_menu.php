 
 <nav>
            <ul class="fancyNav">
               
                <li <?php if($_GET['page'] == 'lar_options_page'){ echo 'class="selected"'; } ?>  ><a href="<?php echo admin_url('admin.php?page=lar_options_page') ?>"><?php echo __('Settings','lar-links-auto-replacer'); ?></a></li>
                <li <?php if($_GET['page'] == 'lar_links_manager'){ echo 'class="selected"'; } ?> ><a href="<?php echo admin_url('admin.php?page=lar_links_manager') ?>"><?php echo __('Links Manager','lar-links-auto-replacer'); ?></a></li>
                <li <?php if($_GET['page'] == 'lar_help'){ echo 'class="selected"'; } ?> ><a href="<?php echo admin_url('admin.php?page=lar_help') ?>"><?php echo __('Help and Support','lar-links-auto-replacer'); ?></a></li>
                
            </ul>
</nav>