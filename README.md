# What is this?

JQuery datatables.net implementation in CodeIgniter.

# Where'd it come from?

http://codeigniter.com/forums/viewthread/164729/ (the patch by zoko2902 is included)

# How do you use it?

	$columns = array('id','name', 'description');
	$joins = 'LEFT JOIN ...';
	$search = 'description';

	echo $this->datatables->generate('table',$columns,'id', $joins, $where, $search);
