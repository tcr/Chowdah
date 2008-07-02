<?php /* Smarty version 2.6.0, created on 2008-02-20 12:51:51
         compiled from left_frame.tpl */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'left_frame.tpl', 65, false),array('modifier', 'strip_tags', 'left_frame.tpl', 112, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array('top2' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<h3 class="package-title"><?php echo $this->_tpl_vars['info']['0']['package']; ?>
</h3>
<div class="tree">
<script language="Javascript">
if (document.getElementById) {
<?php if (isset($this->_sections['p'])) unset($this->_sections['p']);
$this->_sections['p']['name'] = 'p';
$this->_sections['p']['loop'] = is_array($_loop=$this->_tpl_vars['info']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['p']['show'] = true;
$this->_sections['p']['max'] = $this->_sections['p']['loop'];
$this->_sections['p']['step'] = 1;
$this->_sections['p']['start'] = $this->_sections['p']['step'] > 0 ? 0 : $this->_sections['p']['loop']-1;
if ($this->_sections['p']['show']) {
    $this->_sections['p']['total'] = $this->_sections['p']['loop'];
    if ($this->_sections['p']['total'] == 0)
        $this->_sections['p']['show'] = false;
} else
    $this->_sections['p']['total'] = 0;
if ($this->_sections['p']['show']):

            for ($this->_sections['p']['index'] = $this->_sections['p']['start'], $this->_sections['p']['iteration'] = 1;
                 $this->_sections['p']['iteration'] <= $this->_sections['p']['total'];
                 $this->_sections['p']['index'] += $this->_sections['p']['step'], $this->_sections['p']['iteration']++):
$this->_sections['p']['rownum'] = $this->_sections['p']['iteration'];
$this->_sections['p']['index_prev'] = $this->_sections['p']['index'] - $this->_sections['p']['step'];
$this->_sections['p']['index_next'] = $this->_sections['p']['index'] + $this->_sections['p']['step'];
$this->_sections['p']['first']      = ($this->_sections['p']['iteration'] == 1);
$this->_sections['p']['last']       = ($this->_sections['p']['iteration'] == $this->_sections['p']['total']);
?>
	<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['subpackage'] == ""): ?>
		var tree = new WebFXTree('<span class="package"><?php echo $this->_tpl_vars['info']['0']['package']; ?>
</span>');
		tree.setBehavior('classic');
	
		<?php if ($this->_tpl_vars['hastodos']): ?>
			var todos = new WebFXTreeItem('To-do List', '<?php echo $this->_tpl_vars['todolink']; ?>
');
			tree.add(todos);
		<?php endif; ?>

		var class_trees = new WebFXTreeItem('Class trees', '<?php echo $this->_tpl_vars['classtreepage']; ?>
.html');
		tree.add(class_trees);

		var elements = new WebFXTreeItem('Index of elements', '<?php echo $this->_tpl_vars['elementindex']; ?>
.html');
		tree.add(elements);

		var parent_node;

		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']): ?>
			var tree_tutorial = new WebFXTreeItem('Tutorial(s)/Manual(s)', '');
			tree.add(tree_tutorial);
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Package-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Class-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Function-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>		
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['hasinterfaces']): ?>
    		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']): ?>
    			var tree_classe = new WebFXTreeItem('Interface(s)', '<?php echo $this->_tpl_vars['packagedoc']; ?>
');
    			
    			<?php if (isset($this->_sections['class'])) unset($this->_sections['class']);
$this->_sections['class']['name'] = 'class';
$this->_sections['class']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['class']['show'] = true;
$this->_sections['class']['max'] = $this->_sections['class']['loop'];
$this->_sections['class']['step'] = 1;
$this->_sections['class']['start'] = $this->_sections['class']['step'] > 0 ? 0 : $this->_sections['class']['loop']-1;
if ($this->_sections['class']['show']) {
    $this->_sections['class']['total'] = $this->_sections['class']['loop'];
    if ($this->_sections['class']['total'] == 0)
        $this->_sections['class']['show'] = false;
} else
    $this->_sections['class']['total'] = 0;
if ($this->_sections['class']['show']):

            for ($this->_sections['class']['index'] = $this->_sections['class']['start'], $this->_sections['class']['iteration'] = 1;
                 $this->_sections['class']['iteration'] <= $this->_sections['class']['total'];
                 $this->_sections['class']['index'] += $this->_sections['class']['step'], $this->_sections['class']['iteration']++):
$this->_sections['class']['rownum'] = $this->_sections['class']['iteration'];
$this->_sections['class']['index_prev'] = $this->_sections['class']['index'] - $this->_sections['class']['step'];
$this->_sections['class']['index_next'] = $this->_sections['class']['index'] + $this->_sections['class']['step'];
$this->_sections['class']['first']      = ($this->_sections['class']['iteration'] == 1);
$this->_sections['class']['last']       = ($this->_sections['class']['iteration'] == $this->_sections['class']['total']);
?>
    			    <?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['is_interface']): ?>
        				var classe = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
        				tree_classe.add(classe);
        			<?php endif; ?>
    			<?php endfor; endif; ?>

    			tree.add(tree_classe);
    		<?php endif; ?>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['hasclasses']): ?>
    		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']): ?>
    			var tree_classe = new WebFXTreeItem('Class(es)', '<?php echo $this->_tpl_vars['packagedoc']; ?>
');
    			
    			<?php if (isset($this->_sections['class'])) unset($this->_sections['class']);
$this->_sections['class']['name'] = 'class';
$this->_sections['class']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['class']['show'] = true;
$this->_sections['class']['max'] = $this->_sections['class']['loop'];
$this->_sections['class']['step'] = 1;
$this->_sections['class']['start'] = $this->_sections['class']['step'] > 0 ? 0 : $this->_sections['class']['loop']-1;
if ($this->_sections['class']['show']) {
    $this->_sections['class']['total'] = $this->_sections['class']['loop'];
    if ($this->_sections['class']['total'] == 0)
        $this->_sections['class']['show'] = false;
} else
    $this->_sections['class']['total'] = 0;
if ($this->_sections['class']['show']):

            for ($this->_sections['class']['index'] = $this->_sections['class']['start'], $this->_sections['class']['iteration'] = 1;
                 $this->_sections['class']['iteration'] <= $this->_sections['class']['total'];
                 $this->_sections['class']['index'] += $this->_sections['class']['step'], $this->_sections['class']['iteration']++):
$this->_sections['class']['rownum'] = $this->_sections['class']['iteration'];
$this->_sections['class']['index_prev'] = $this->_sections['class']['index'] - $this->_sections['class']['step'];
$this->_sections['class']['index_next'] = $this->_sections['class']['index'] + $this->_sections['class']['step'];
$this->_sections['class']['first']      = ($this->_sections['class']['iteration'] == 1);
$this->_sections['class']['last']       = ($this->_sections['class']['iteration'] == $this->_sections['class']['total']);
?>
    			    <?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['is_class']): ?>
        				var classe = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
        				tree_classe.add(classe);
        			<?php endif; ?>
    			<?php endfor; endif; ?>
    	
    			tree.add(tree_classe);
    		<?php endif; ?>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['functions']): ?>
			var tree_function = new WebFXTreeItem('Function(s)', '<?php echo $this->_tpl_vars['packagedoc']; ?>
');
			
			<?php if (isset($this->_sections['nonclass'])) unset($this->_sections['nonclass']);
$this->_sections['nonclass']['name'] = 'nonclass';
$this->_sections['nonclass']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['nonclass']['show'] = true;
$this->_sections['nonclass']['max'] = $this->_sections['nonclass']['loop'];
$this->_sections['nonclass']['step'] = 1;
$this->_sections['nonclass']['start'] = $this->_sections['nonclass']['step'] > 0 ? 0 : $this->_sections['nonclass']['loop']-1;
if ($this->_sections['nonclass']['show']) {
    $this->_sections['nonclass']['total'] = $this->_sections['nonclass']['loop'];
    if ($this->_sections['nonclass']['total'] == 0)
        $this->_sections['nonclass']['show'] = false;
} else
    $this->_sections['nonclass']['total'] = 0;
if ($this->_sections['nonclass']['show']):

            for ($this->_sections['nonclass']['index'] = $this->_sections['nonclass']['start'], $this->_sections['nonclass']['iteration'] = 1;
                 $this->_sections['nonclass']['iteration'] <= $this->_sections['nonclass']['total'];
                 $this->_sections['nonclass']['index'] += $this->_sections['nonclass']['step'], $this->_sections['nonclass']['iteration']++):
$this->_sections['nonclass']['rownum'] = $this->_sections['nonclass']['iteration'];
$this->_sections['nonclass']['index_prev'] = $this->_sections['nonclass']['index'] - $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['index_next'] = $this->_sections['nonclass']['index'] + $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['first']      = ($this->_sections['nonclass']['iteration'] == 1);
$this->_sections['nonclass']['last']       = ($this->_sections['nonclass']['iteration'] == $this->_sections['nonclass']['total']);
?>
				var fic = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions'][$this->_sections['nonclass']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions'][$this->_sections['nonclass']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
				tree_function.add(fic);
			<?php endfor; endif; ?>
		
			tree.add(tree_function);
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['files']): ?>
			var tree_file = new WebFXTreeItem('File(s)', '<?php echo ((is_array($_tmp=$this->_tpl_vars['packagedoc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
	
			<?php if (isset($this->_sections['nonclass'])) unset($this->_sections['nonclass']);
$this->_sections['nonclass']['name'] = 'nonclass';
$this->_sections['nonclass']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['nonclass']['show'] = true;
$this->_sections['nonclass']['max'] = $this->_sections['nonclass']['loop'];
$this->_sections['nonclass']['step'] = 1;
$this->_sections['nonclass']['start'] = $this->_sections['nonclass']['step'] > 0 ? 0 : $this->_sections['nonclass']['loop']-1;
if ($this->_sections['nonclass']['show']) {
    $this->_sections['nonclass']['total'] = $this->_sections['nonclass']['loop'];
    if ($this->_sections['nonclass']['total'] == 0)
        $this->_sections['nonclass']['show'] = false;
} else
    $this->_sections['nonclass']['total'] = 0;
if ($this->_sections['nonclass']['show']):

            for ($this->_sections['nonclass']['index'] = $this->_sections['nonclass']['start'], $this->_sections['nonclass']['iteration'] = 1;
                 $this->_sections['nonclass']['iteration'] <= $this->_sections['nonclass']['total'];
                 $this->_sections['nonclass']['index'] += $this->_sections['nonclass']['step'], $this->_sections['nonclass']['iteration']++):
$this->_sections['nonclass']['rownum'] = $this->_sections['nonclass']['iteration'];
$this->_sections['nonclass']['index_prev'] = $this->_sections['nonclass']['index'] - $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['index_next'] = $this->_sections['nonclass']['index'] + $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['first']      = ($this->_sections['nonclass']['iteration'] == 1);
$this->_sections['nonclass']['last']       = ($this->_sections['nonclass']['iteration'] == $this->_sections['nonclass']['total']);
?>
				var file = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files'][$this->_sections['nonclass']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files'][$this->_sections['nonclass']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
				tree_file.add(file);
			<?php endfor; endif; ?>
	
			tree.add(tree_file);
		<?php endif; ?>

	<?php else: ?>
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['subpackagetutorial']): ?>
			var subpackagetree = new WebFXTreeItem('<span class="sub-package"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['subpackagetutorialtitle'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
</span>', '<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['subpackagetutorialnoa']; ?>
');
		<?php else: ?>
			var subpackagetree = new WebFXTreeItem('<span class="sub-package"><?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['subpackage']; ?>
</span>', '<?php echo ((is_array($_tmp=$this->_tpl_vars['packagedoc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
		<?php endif; ?>

		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']): ?>
			var tree_tutorial = new WebFXTreeItem('Tutorial(s)/Manual(s)', '');
			tree.add(tree_tutorial);
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Package-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['pkg'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Class-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['cls'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc']): ?>
				var tree_inner_tutorial = new WebFXTreeItem('Function-level', '');
				tree_tutorial.add(tree_inner_tutorial);
				
				parent_node = tree_inner_tutorial;
				<?php if (isset($this->_sections['ext'])) unset($this->_sections['ext']);
$this->_sections['ext']['name'] = 'ext';
$this->_sections['ext']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ext']['show'] = true;
$this->_sections['ext']['max'] = $this->_sections['ext']['loop'];
$this->_sections['ext']['step'] = 1;
$this->_sections['ext']['start'] = $this->_sections['ext']['step'] > 0 ? 0 : $this->_sections['ext']['loop']-1;
if ($this->_sections['ext']['show']) {
    $this->_sections['ext']['total'] = $this->_sections['ext']['loop'];
    if ($this->_sections['ext']['total'] == 0)
        $this->_sections['ext']['show'] = false;
} else
    $this->_sections['ext']['total'] = 0;
if ($this->_sections['ext']['show']):

            for ($this->_sections['ext']['index'] = $this->_sections['ext']['start'], $this->_sections['ext']['iteration'] = 1;
                 $this->_sections['ext']['iteration'] <= $this->_sections['ext']['total'];
                 $this->_sections['ext']['index'] += $this->_sections['ext']['step'], $this->_sections['ext']['iteration']++):
$this->_sections['ext']['rownum'] = $this->_sections['ext']['iteration'];
$this->_sections['ext']['index_prev'] = $this->_sections['ext']['index'] - $this->_sections['ext']['step'];
$this->_sections['ext']['index_next'] = $this->_sections['ext']['index'] + $this->_sections['ext']['step'];
$this->_sections['ext']['first']      = ($this->_sections['ext']['iteration'] == 1);
$this->_sections['ext']['last']       = ($this->_sections['ext']['iteration'] == $this->_sections['ext']['total']);
?>
					<?php echo $this->_tpl_vars['info'][$this->_sections['p']['index']]['tutorials']['proc'][$this->_sections['ext']['index']]; ?>

				<?php endfor; endif; ?>
			<?php endif; ?>		
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']): ?>
			var subpackagetree_classe = new WebFXTreeItem('Class(es)', '<?php echo ((is_array($_tmp=$this->_tpl_vars['packagedoc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
			
			<?php if (isset($this->_sections['class'])) unset($this->_sections['class']);
$this->_sections['class']['name'] = 'class';
$this->_sections['class']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['class']['show'] = true;
$this->_sections['class']['max'] = $this->_sections['class']['loop'];
$this->_sections['class']['step'] = 1;
$this->_sections['class']['start'] = $this->_sections['class']['step'] > 0 ? 0 : $this->_sections['class']['loop']-1;
if ($this->_sections['class']['show']) {
    $this->_sections['class']['total'] = $this->_sections['class']['loop'];
    if ($this->_sections['class']['total'] == 0)
        $this->_sections['class']['show'] = false;
} else
    $this->_sections['class']['total'] = 0;
if ($this->_sections['class']['show']):

            for ($this->_sections['class']['index'] = $this->_sections['class']['start'], $this->_sections['class']['iteration'] = 1;
                 $this->_sections['class']['iteration'] <= $this->_sections['class']['total'];
                 $this->_sections['class']['index'] += $this->_sections['class']['step'], $this->_sections['class']['iteration']++):
$this->_sections['class']['rownum'] = $this->_sections['class']['iteration'];
$this->_sections['class']['index_prev'] = $this->_sections['class']['index'] - $this->_sections['class']['step'];
$this->_sections['class']['index_next'] = $this->_sections['class']['index'] + $this->_sections['class']['step'];
$this->_sections['class']['first']      = ($this->_sections['class']['iteration'] == 1);
$this->_sections['class']['last']       = ($this->_sections['class']['iteration'] == $this->_sections['class']['total']);
?>
				var classe = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['classes'][$this->_sections['class']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
				subpackagetree_classe.add(classe);
			<?php endfor; endif; ?>
			
			subpackagetree.add(subpackagetree_classe);
		<?php endif; ?>

		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['functions']): ?>
			var subpackagetree_function = new WebFXTreeItem('Function(s)', '<?php echo $this->_tpl_vars['packagedoc']; ?>
');
			
			<?php if (isset($this->_sections['nonclass'])) unset($this->_sections['nonclass']);
$this->_sections['nonclass']['name'] = 'nonclass';
$this->_sections['nonclass']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['nonclass']['show'] = true;
$this->_sections['nonclass']['max'] = $this->_sections['nonclass']['loop'];
$this->_sections['nonclass']['step'] = 1;
$this->_sections['nonclass']['start'] = $this->_sections['nonclass']['step'] > 0 ? 0 : $this->_sections['nonclass']['loop']-1;
if ($this->_sections['nonclass']['show']) {
    $this->_sections['nonclass']['total'] = $this->_sections['nonclass']['loop'];
    if ($this->_sections['nonclass']['total'] == 0)
        $this->_sections['nonclass']['show'] = false;
} else
    $this->_sections['nonclass']['total'] = 0;
if ($this->_sections['nonclass']['show']):

            for ($this->_sections['nonclass']['index'] = $this->_sections['nonclass']['start'], $this->_sections['nonclass']['iteration'] = 1;
                 $this->_sections['nonclass']['iteration'] <= $this->_sections['nonclass']['total'];
                 $this->_sections['nonclass']['index'] += $this->_sections['nonclass']['step'], $this->_sections['nonclass']['iteration']++):
$this->_sections['nonclass']['rownum'] = $this->_sections['nonclass']['iteration'];
$this->_sections['nonclass']['index_prev'] = $this->_sections['nonclass']['index'] - $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['index_next'] = $this->_sections['nonclass']['index'] + $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['first']      = ($this->_sections['nonclass']['iteration'] == 1);
$this->_sections['nonclass']['last']       = ($this->_sections['nonclass']['iteration'] == $this->_sections['nonclass']['total']);
?>
				var fic = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions'][$this->_sections['nonclass']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['functions'][$this->_sections['nonclass']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
				subpackagetree_function.add(fic);
			<?php endfor; endif; ?>
			
			subpackagetree.add(subpackagetree_function);
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['info'][$this->_sections['p']['index']]['files']): ?>
			var subpackagetree_file = new WebFXTreeItem('File(s)', '<?php echo ((is_array($_tmp=$this->_tpl_vars['packagedoc'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
			
			<?php if (isset($this->_sections['nonclass'])) unset($this->_sections['nonclass']);
$this->_sections['nonclass']['name'] = 'nonclass';
$this->_sections['nonclass']['loop'] = is_array($_loop=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['nonclass']['show'] = true;
$this->_sections['nonclass']['max'] = $this->_sections['nonclass']['loop'];
$this->_sections['nonclass']['step'] = 1;
$this->_sections['nonclass']['start'] = $this->_sections['nonclass']['step'] > 0 ? 0 : $this->_sections['nonclass']['loop']-1;
if ($this->_sections['nonclass']['show']) {
    $this->_sections['nonclass']['total'] = $this->_sections['nonclass']['loop'];
    if ($this->_sections['nonclass']['total'] == 0)
        $this->_sections['nonclass']['show'] = false;
} else
    $this->_sections['nonclass']['total'] = 0;
if ($this->_sections['nonclass']['show']):

            for ($this->_sections['nonclass']['index'] = $this->_sections['nonclass']['start'], $this->_sections['nonclass']['iteration'] = 1;
                 $this->_sections['nonclass']['iteration'] <= $this->_sections['nonclass']['total'];
                 $this->_sections['nonclass']['index'] += $this->_sections['nonclass']['step'], $this->_sections['nonclass']['iteration']++):
$this->_sections['nonclass']['rownum'] = $this->_sections['nonclass']['iteration'];
$this->_sections['nonclass']['index_prev'] = $this->_sections['nonclass']['index'] - $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['index_next'] = $this->_sections['nonclass']['index'] + $this->_sections['nonclass']['step'];
$this->_sections['nonclass']['first']      = ($this->_sections['nonclass']['iteration'] == 1);
$this->_sections['nonclass']['last']       = ($this->_sections['nonclass']['iteration'] == $this->_sections['nonclass']['total']);
?>
				var file = new WebFXTreeItem('<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files'][$this->_sections['nonclass']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
', '<?php echo ((is_array($_tmp=$this->_tpl_vars['info'][$this->_sections['p']['index']]['files'][$this->_sections['nonclass']['index']]['link'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
');
				subpackagetree_file.add(file);
			<?php endfor; endif; ?>
		
			subpackagetree.add(subpackagetree_file);
		<?php endif; ?>
	
	  tree.add(subpackagetree);
	<?php endif; ?>
<?php endfor; endif; ?>

document.write(tree);
}
</script>
</div>
<p class="notes">
	Generated by 
	<a href="<?php echo $this->_tpl_vars['phpdocwebsite']; ?>
" target="_blank">phpDocumentor <span class="field"><?php echo $this->_tpl_vars['phpdocversion']; ?>
</span></a>
</p>
</body>
</html>