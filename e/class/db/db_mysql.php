<?php
define('InEmpireCMSDbSql',TRUE);

//------------------------- ���ݿ� -------------------------

//�������ݿ�
function do_dbconnect($dbhost,$dbport,$dbusername,$dbpassword,$dbname){
	global $ecms_config;
	$dblocalhost=$dbhost;
	//�˿�
	if($dbport)
	{
		$dblocalhost.=':'.$dbport;
	}
	$dblink=@mysql_connect($dblocalhost,$dbusername,$dbpassword);
	if(!$dblink)
	{
		echo"Cann't connect to DB!";
		exit();
	}
	//����
	if($ecms_config['db']['dbver']>='4.1')
	{
		$q='';
		if($ecms_config['db']['setchar'])
		{
			$q='character_set_connection='.$ecms_config['db']['setchar'].',character_set_results='.$ecms_config['db']['setchar'].',character_set_client=binary';
		}
		if($ecms_config['db']['dbver']>='5.0')
		{
			$q.=(empty($q)?'':',').'sql_mode=\'\'';
		}
		if($q)
		{
			@mysql_query('SET '.$q,$dblink);
		}
	}
	@mysql_select_db($dbname,$dblink);
	return $dblink;
}

//�ر����ݿ�
function do_dbclose($dblink){
	if($dblink)
	{
		@mysql_close($dblink);
	}
}

//���ñ���
function do_DoSetDbChar($dbchar,$dblink){
	@mysql_query('set character_set_connection='.$dbchar.',character_set_results='.$dbchar.',character_set_client=binary;',$dblink);
}

//ȡ��mysql�汾
function do_eGetDBVer($selectdb=0){
	global $empire;
	if($selectdb&&$empire)
	{
		$getdbver=$empire->egetdbver();
	}
	else
	{
		$getdbver=@mysql_get_server_info();
	}
	return $getdbver;
}

//��ͨ����
function do_dbconnect_common($dbhost,$dbport,$dbusername,$dbpassword,$dbname=''){
	global $ecms_config;
	$dblocalhost=$dbhost;
	//�˿�
	if($dbport)
	{
		$dblocalhost.=':'.$dbport;
	}
	$dblink=@mysql_connect($dblocalhost,$dbusername,$dbpassword);
	return $dblink;
}

function do_dbquery_common($query,$dblink,$ecms=0){
	global $ecms_config;
	if($ecms==0)
	{
		$sql=mysql_query($query,$dblink);
	}
	else
	{
		$sql=mysql_query($query,$dblink) or die($ecms_config['db']['showerror']==1?str_replace($GLOBALS['dbtbpre'],'***_',mysql_error().'<br>'.$query):'DbError');
	}
	return $sql;
}

function do_dbfetch_common($sql){
	$r=mysql_fetch_array($sql);
	return $r;
}

function do_dblastid_common($dblink){
	$id=mysql_insert_id($dblink);
	if($id<0)
	{
		$sql=do_dbquery_common('SELECT last_insert_id() as total',$dblink);
		$r=do_dbfetch_common($sql);
		$id=$r['total'];
	}
	return $id;
}

//ѡ�����ݿ�
function do_eUseDb($dbname,$dblink,$query=0){
	if($query)
	{
		$usedb=do_dbquery_common('use `'.$dbname.'`',$dblink);
	}
	else
	{
		$usedb=@mysql_select_db($dbname,$dblink);
	}
	return $usedb;
}



//------------------------- ���ݿ���� -------------------------

class mysqlquery
{
	var $dblink;
	var $sql;//sql���ִ�н��
	var $query;//sql���
	var $num;//���ؼ�¼��
	var $r;//��������
	var $id;//�������ݿ�id��
	//ִ��mysql_query()���
	function query($query){
		global $ecms_config;
		$this->sql=mysql_query($query,return_dblink($query)) or die($ecms_config['db']['showerror']==1?str_replace($GLOBALS['dbtbpre'],'***_',mysql_error().'<br>'.$query):'DbError');
		return $this->sql;
	}
	//ִ��mysql_query()���2
	function query1($query){
		$this->sql=mysql_query($query,return_dblink($query));
		return $this->sql;
	}
	//ִ��mysql_query()���(ѡ�����ݿ�USE)
	function usequery($query){
		global $ecms_config;
		$this->sql=mysql_query($query,$GLOBALS['link']) or die($ecms_config['db']['showerror']==1?str_replace($GLOBALS['dbtbpre'],'***_',mysql_error().'<br>'.$query):'DbError');
		if($GLOBALS['linkrd'])
		{
			mysql_query($query,$GLOBALS['linkrd']);
		}
		return $this->sql;
	}
	//ִ��mysql_query()���(�������ݿ�)
	function updatesql($query){
		global $ecms_config;
		$this->sql=mysql_query($query,return_dblink($query)) or die($ecms_config['db']['showerror']==1?str_replace($GLOBALS['dbtbpre'],'***_',mysql_error().'<br>'.$query):'DbError');
		return $this->sql;
	}
	//ִ��mysql_fetch_array()
	function fetch($sql)//�˷����Ĳ�����$sql����sql���ִ�н��
	{
		$this->r=mysql_fetch_array($sql);
		return $this->r;
	}
	//ִ��fetchone(mysql_fetch_array())
	//�˷�����fetch()��������:1���˷����Ĳ�����$query����sql��� 
	//2���˷�������while(),for()���ݿ�ָ�벻���Զ����ƣ���fetch()�����Զ����ơ�
	function fetch1($query)
	{
		$this->sql=$this->query($query);
		$this->r=mysql_fetch_array($this->sql);
		return $this->r;
	}
	//ִ��mysql_num_rows()
	function num($query)//����Ĳ�����$query����sql���
	{
		$this->sql=$this->query($query);
		$this->num=mysql_num_rows($this->sql);
		return $this->num;
	}
	//ִ��numone(mysql_num_rows())
	//�˷�����num()�������ǣ�1���˷����Ĳ�����$sql����sql����ִ�н����
	function num1($sql)
	{
		$this->num=mysql_num_rows($sql);
		return $this->num;
	}
	//ִ��numone(mysql_num_rows())
	//ͳ�Ƽ�¼��
	function gettotal($query)
	{
		$this->r=$this->fetch1($query);
		return $this->r['total'];
	}
	//ִ��free(mysql_result_free())
	//�˷����Ĳ�����$sql����sql����ִ�н����ֻ�����õ�mysql_fetch_array���������
	function free($sql)
	{
		mysql_free_result($sql);
	}
	//ִ��seek(mysql_data_seek())
	//�˷����Ĳ�����$sql����sql����ִ�н��,$pitΪִ��ָ���ƫ����
	function seek($sql,$pit)
	{
		mysql_data_seek($sql,$pit);
	}
	//ִ��id(mysql_insert_id())
	function lastid()//ȡ�����һ��ִ��mysql���ݿ�id��
	{
		$this->id=mysql_insert_id($GLOBALS['link']);
		if($this->id<0)
		{
			$this->id=$this->gettotal('SELECT last_insert_id() as total');
		}
		return $this->id;
	}
	//����Ӱ������(mysql_affected_rows())
	function affectnum()//ȡ�ò������ݱ�����Ӱ��ļ�¼��
	{
		return mysql_affected_rows($GLOBALS['link']);
	}
	//ִ��escape_string()����
	function EDbEscapeStr($str){
		$str=mysql_real_escape_string($str);
		return $str;
	}
	//ȡ�����ݿ�汾
	function egetdbver()
	{
		$this->r=$this->fetch1('select version() as version');
		return $this->r['version'];
	}
}
?>