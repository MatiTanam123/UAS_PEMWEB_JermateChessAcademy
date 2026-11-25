<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../config/koneksi.php';
require __DIR__.'/../includes/admin_layout.php';
$u=require_role('admin');

$entities=[
 'program'=>['title'=>'Program','pk'=>'id_program','fields'=>[
  'id_admin'=>['select','admin','id_admin','nama_admin',true],'nama_program'=>['text'],'tingkat'=>['enum',['Beginner','Intermediate','Professional']],
  'deskripsi'=>['textarea'],'biaya'=>['number'],'durasi_minggu'=>['number'],'kuota'=>['number'],'status_aktif'=>['bool']]],
 'peserta'=>['title'=>'Peserta','pk'=>'id_peserta','fields'=>[
  'nama_lengkap'=>['text'],'username'=>['text'],'email'=>['email'],'no_hp'=>['text'],'tanggal_lahir'=>['date'],
  'jenis_kelamin'=>['enum',['Laki-laki','Perempuan'],true],'alamat'=>['textarea'],'foto_profil'=>['file'],
  'status_akun'=>['enum',['aktif','nonaktif','ditangguhkan']]]],
 'pelatih'=>['title'=>'Pelatih','pk'=>'id_pelatih','fields'=>[
  'id_admin'=>['select','admin','id_admin','nama_admin',true],'nama_pelatih'=>['text'],'email'=>['email'],'no_hp'=>['text'],
  'spesialisasi'=>['text'],'biografi'=>['textarea'],'foto'=>['file'],'pengalaman_tahun'=>['number'],'status_aktif'=>['bool']]],
 'jadwal'=>['title'=>'Jadwal','pk'=>'id_jadwal','fields'=>[
  'id_program'=>['select','program','id_program','nama_program'],'id_pelatih'=>['select','pelatih','id_pelatih','nama_pelatih',true],
  'id_admin'=>['select','admin','id_admin','nama_admin',true],'tanggal_latihan'=>['date'],'waktu_mulai'=>['time'],
  'waktu_selesai'=>['time'],'lokasi'=>['text'],'kapasitas'=>['number'],'status_jadwal'=>['enum',['Tersedia','Penuh','Selesai','Dibatalkan']]]],
 'pendaftaran'=>['title'=>'Pendaftaran','pk'=>'id_pendaftaran','fields'=>[
  'id_peserta'=>['select','peserta','id_peserta','nama_lengkap'],'id_program'=>['select','program','id_program','nama_program'],
  'id_jadwal'=>['select','jadwal','id_jadwal','tanggal_latihan',true],
  'status_pendaftaran'=>['enum',['menunggu','disetujui','ditolak','selesai']],'catatan'=>['textarea']]],
 'pembayaran'=>['title'=>'Pembayaran','pk'=>'id_pembayaran','fields'=>[
  'id_pendaftaran'=>['select','pendaftaran','id_pendaftaran','id_pendaftaran'],'id_admin'=>['select','admin','id_admin','nama_admin',true],
  'kode_transaksi'=>['text'],'jumlah_pembayaran'=>['number'],'metode_pembayaran'=>['enum',['Transfer','Tunai','QRIS']],
  'bukti_pembayaran'=>['text'],'status_pembayaran'=>['enum',['menunggu','terverifikasi','ditolak']],
  'tanggal_bayar'=>['datetime',true],'tanggal_verifikasi'=>['datetime',true],'catatan_verifikasi'=>['textarea']]],
 'kehadiran'=>['title'=>'Kehadiran','pk'=>'id_kehadiran','fields'=>[
  'id_pendaftaran'=>['select','pendaftaran','id_pendaftaran','id_pendaftaran'],'id_jadwal'=>['select','jadwal','id_jadwal','tanggal_latihan'],
  'id_peserta'=>['select','peserta','id_peserta','nama_lengkap'],'status_kehadiran'=>['enum',['Hadir','Izin','Sakit','Alpa']],
  'waktu_absen'=>['datetime',true],'keterangan'=>['textarea']]],
 'galeri'=>['title'=>'Galeri','pk'=>'id_galeri','fields'=>[
  'id_admin'=>['select','admin','id_admin','nama_admin',true],'judul'=>['text'],'deskripsi'=>['textarea'],
  'lokasi_url'=>['text'],'kategori_kegiatan'=>['text'],'tanggal_kegiatan'=>['date',true]]],
 'testimoni'=>['title'=>'Testimoni','pk'=>'id_testimoni','fields'=>[
  'id_peserta'=>['select','peserta','id_peserta','nama_lengkap'],'id_program'=>['select','program','id_program','nama_program',true],
  'rating'=>['number'],'isi_testimoni'=>['textarea'],'status'=>['enum',['menunggu','ditampilkan','ditolak']]]],
 'notifikasi'=>['title'=>'Notifikasi','pk'=>'id_notifikasi','fields'=>[
  'id_peserta'=>['select','peserta','id_peserta','nama_lengkap',true],'id_admin'=>['select','admin','id_admin','nama_admin',true],
  'judul'=>['text'],'pesan'=>['textarea'],'tipe'=>['text'],'status_baca'=>['bool']]],
 'pengaturan'=>['title'=>'Pengaturan','pk'=>'id_pengaturan','fields'=>[
  'id_admin'=>['select','admin','id_admin','nama_admin',true],'kunci'=>['text'],'nilai'=>['textarea'],'deskripsi'=>['text']]]
];
$key=$_GET['entity']??'';
if(!isset($entities[$key])){http_response_code(404);exit('Modul tidak ditemukan.');}
$c=$entities[$key];$table=$key;$pk=$c['pk'];$fields=$c['fields'];
$requiredFields=['program'=>['nama_program','tingkat','biaya','durasi_minggu','kuota'],'peserta'=>['nama_lengkap','username','email'],'pelatih'=>['nama_pelatih'],'jadwal'=>['id_program','tanggal_latihan','waktu_mulai','waktu_selesai','kapasitas'],'pendaftaran'=>['id_peserta','id_program'],'pembayaran'=>['id_pendaftaran','jumlah_pembayaran','metode_pembayaran'],'kehadiran'=>['id_pendaftaran','id_jadwal','id_peserta'],'galeri'=>['judul'],'testimoni'=>['id_peserta','rating','isi_testimoni'],'notifikasi'=>['judul','pesan'],'pengaturan'=>['kunci']][$key]??[];
function field_value(string $name,array $spec): string {
 $type=$spec[0];return $name==='password'?'':match($type){'textarea'=>'textarea','enum'=>'select','bool'=>'select','select'=>'select',default=>match($type){'datetime'=>'datetime-local',default=>$type}};
}
function option_rows(mysqli $db,array $spec): array {
 if(($spec[0]??'')!=='select')return [];
 [$type,$table,$id,$label]=$spec;$sql="SELECT `$id` AS v, `$label` AS t FROM `$table` ORDER BY `$label` LIMIT 500";
 return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}
function render_field(mysqli $db,string $name,array $spec,$value=null): void {
 $type=$spec[0];$optional=in_array(true,array_slice($spec,1),true);$label=ucwords(str_replace('_',' ',$name));$val=(string)($value??'');
 $required=in_array($name,$GLOBALS['requiredFields']??[],true);$req=$required?' required':'';
 echo '<div class="field"><label>'.e($label).($required?' *':'').'</label>';
 if($type==='file'){
  // Foto bersifat opsional ketika edit agar foto lama tidak terhapus tanpa pengganti.
  echo '<input type="file" name="'.e($name).'" accept="image/jpeg,image/png,image/webp">';
  echo '<small>Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.</small>';
  if($val!==''){
   $src='../'.ltrim($val,'/');
   echo '<div class="current-image" style="margin-top:10px">';
   echo '<img src="'.e($src).'" alt="'.e($label).'" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #ddd">';
   echo '<div><small>Foto saat ini</small></div></div>';
  }
 }elseif($type==='textarea'){echo '<textarea name="'.e($name).'"'.$req.'>'.e($val).'</textarea>';}
 elseif($type==='enum'||$type==='bool'){
  $opts=$type==='bool'?['1'=>'Aktif','0'=>'Nonaktif']:array_combine($spec[1],$spec[1]);
  echo '<select name="'.e($name).'"'.$req.'>';if($optional)echo '<option value="">- Tidak dipilih -</option>';
  foreach($opts as $v=>$t)echo '<option value="'.e($v).'" '.((string)$v===$val?'selected':'').'>'.e($t).'</option>';echo '</select>';
 }elseif($type==='select'){
  $opts=option_rows($GLOBALS['db'],$spec);echo '<select name="'.e($name).'"'.$req.'>';if($optional)echo '<option value="">- Tidak dipilih -</option>';
  foreach($opts as $o)echo '<option value="'.e($o['v']).'" '.((string)$o['v']===$val?'selected':'').'>'.e($o['t']).' ('.e($o['v']).')</option>';echo '</select>';
 }else{$input=$type==='datetime'?'datetime-local':$type;$v=$val;if($type==='datetime'&&$v)$v=str_replace(' ','T',substr($v,0,16));echo '<input type="'.e($input).'" name="'.e($name).'" value="'.e($v).'"'.$req.'>';}
 echo '</div>';
}

/** Upload foto ke Backend/uploads/{peserta|pelatih}. Mengembalikan path relatif untuk DB. */
function upload_profile_image(string $field,string $folder): ?string {
 if(!isset($_FILES[$field]) || !is_array($_FILES[$field])) return null;
 $file=$_FILES[$field];
 if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return null;
 if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('Upload foto gagal. Silakan coba kembali.');
 if(($file['size']??0)>2*1024*1024) throw new RuntimeException('Ukuran foto maksimal 2 MB.');
 if(!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('File upload tidak valid.');
 $finfo=new finfo(FILEINFO_MIME_TYPE);$mime=$finfo->file($file['tmp_name']);
 $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
 if(!isset($allowed[$mime])) throw new RuntimeException('Format foto harus JPG, JPEG, PNG, atau WEBP.');
 if(@getimagesize($file['tmp_name'])===false) throw new RuntimeException('File yang dipilih bukan gambar yang valid.');
 $base=__DIR__.'/../uploads/'.$folder.'/';
 if(!is_dir($base) && !mkdir($base,0755,true) && !is_dir($base)) throw new RuntimeException('Folder upload foto tidak dapat dibuat.');
 $filename=bin2hex(random_bytes(16)).'.'.$allowed[$mime];
 if(!move_uploaded_file($file['tmp_name'],$base.$filename)) throw new RuntimeException('Foto gagal disimpan ke server.');
 return 'uploads/'.$folder.'/'.$filename;
}

/** Menghapus hanya file gambar yang berada di folder uploads milik aplikasi. */
function delete_profile_image(?string $relativePath): void {
 if(!$relativePath || !preg_match('~^uploads/(peserta|pelatih)/[a-f0-9]{32}\.(jpg|png|webp)$~i',$relativePath)) return;
 $base=realpath(__DIR__.'/../uploads');$file=realpath(__DIR__.'/../'.$relativePath);
 if($base && $file && str_starts_with($file,$base.DIRECTORY_SEPARATOR) && is_file($file)) @unlink($file);
}
function schedule_registration_options(mysqli $db, string $programId = '', string $scheduleId = ''): array {
 $sql="SELECT d.id_pendaftaran,d.id_program,d.id_jadwal,d.status_pendaftaran,p.nama_lengkap,p.username
       FROM pendaftaran d JOIN peserta p ON p.id_peserta=d.id_peserta
       WHERE d.status_pendaftaran='disetujui'
         AND (d.id_jadwal IS NULL OR d.id_jadwal=?)
       ORDER BY p.nama_lengkap";
 $st=$db->prepare($sql);$st->bind_param('s',$scheduleId);$st->execute();$rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);$st->close();
 return $rows;
}
function render_schedule_participants(mysqli $db, string $programId = '', string $scheduleId = ''): void {
 $options=schedule_registration_options($db,$programId,$scheduleId);
 $selected=[];
 if($scheduleId!==''){
  $st=$db->prepare("SELECT id_pendaftaran FROM pendaftaran WHERE id_jadwal=? AND status_pendaftaran='disetujui'");
  $st->bind_param('s',$scheduleId);$st->execute();
  foreach($st->get_result()->fetch_all(MYSQLI_ASSOC) as $row)$selected[]=$row['id_pendaftaran'];
  $st->close();
 }
 echo '<div class="schedule-participant-picker">';
 echo '<div class="schedule-picker-heading"><strong>Peserta untuk Jadwal Ini</strong><span>Pilih peserta yang sudah disetujui pada program ini.</span></div>';
 echo '<div class="schedule-picker-list">';
 echo '<p class="schedule-picker-empty"'.($options?' hidden':'').'>Belum ada peserta disetujui yang tersedia untuk program ini.</p>';
 foreach($options as $row){
  $checked=in_array($row['id_pendaftaran'],$selected,true)?' checked':'';
  $disabled=($programId!=='' && $row['id_program']!==$programId)?' disabled':'';
  $name=e($row['nama_lengkap']);$username=e($row['username']);$registration=e($row['id_pendaftaran']);$program=e($row['id_program']);
  echo '<label class="schedule-participant-option" data-program="'.$program.'">';
  echo '<input type="checkbox" name="peserta_pendaftaran[]" value="'.$registration.'"'.$checked.$disabled.'>';
  echo '<span><strong>'.$name.'</strong><small>'.$username.' · '.$registration.'</small></span>';
  echo '</label>';
 }
 echo '</div><small class="schedule-picker-note">Peserta yang sudah memiliki jadwal lain tidak ditampilkan.</small></div>';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();$action=$_POST['action']??'';
 try{
  if($action==='create'||$action==='update'){
   $values=[];foreach($fields as $name=>$spec){
    // File ditangani terpisah melalui $_FILES, bukan $_POST.
    if($spec[0]==='file') continue;
    $v=trim((string)($_POST[$name]??''));
    if($v===''&&in_array($name,$requiredFields,true))throw new RuntimeException("Kolom $name wajib diisi.");
    if($v===''&&($spec[0]==='select'||$spec[0]==='date'||$spec[0]==='datetime'))$v=null;
    if($spec[0]==='enum'&&$v==='')$v=$spec[1][0];
    if($spec[0]==='number'&&$v==='')$v='0';
    if($spec[0]==='bool'&&$v==='')$v=($key==='notifikasi'&&$name==='status_baca')?'0':'1';
    if($spec[0]==='datetime'&&$v!==null)$v=str_replace('T',' ',$v).(strlen($v)===16?':00':'');
    if($spec[0]==='enum'&&$v!==null&&!in_array($v,$spec[1],true))throw new RuntimeException("Nilai $name tidak valid.");
    if($spec[0]==='bool')$v=($v==='1')?'1':'0';
    if($v!==null)$values[$name]=$v;
   }
   // Peserta memakai kolom foto_profil; Pelatih memakai kolom foto.
   $imageField=$key==='peserta'?'foto_profil':($key==='pelatih'?'foto':null);
   $newImage=null;$oldImage=null;
   if($imageField!==null){
    $uploadFolder=$key;
    if($action==='update'){
     $editId=(string)($_POST['id']??'');
     if($editId==='')throw new RuntimeException('ID data tidak valid.');
     $oldStmt=$db->prepare("SELECT `$imageField` FROM `$table` WHERE `$pk`=? LIMIT 1");
     $oldStmt->bind_param('s',$editId);$oldStmt->execute();$oldRow=$oldStmt->get_result()->fetch_assoc();$oldStmt->close();
     if(!$oldRow)throw new RuntimeException('Data yang akan diperbarui tidak ditemukan.');
     $oldImage=$oldRow[$imageField]??null;
    }
    $newImage=upload_profile_image($imageField,$uploadFolder);
    if($newImage!==null)$values[$imageField]=$newImage;
    elseif($action==='create')$values[$imageField]=null;
   }
   if($key==='program'||$key==='pelatih'||$key==='galeri'||$key==='jadwal'||$key==='pengaturan')$values['id_admin']=$values['id_admin']??$u['id'];
   if($key==='pembayaran'&&empty($values['kode_transaksi']))$values['kode_transaksi']='JM-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
   if($key==='notifikasi'&&empty($values['id_peserta']))$values['id_peserta']=null;
   if($key==='notifikasi'&&empty($values['id_admin']))$values['id_admin']=$u['id'];
   if($key==='pembayaran'&&empty($values['tanggal_bayar']))$values['tanggal_bayar']=date('Y-m-d H:i:s');
   // Validasi lintas tabel untuk mencegah data bisnis yang tidak konsisten.
   if($key==='program'){
    if((float)($values['biaya']??0)<0)throw new RuntimeException('Biaya program tidak boleh negatif.');
    if((int)($values['kuota']??0)<1 || (int)($values['durasi_minggu']??0)<1)throw new RuntimeException('Kuota dan durasi program minimal 1.');
   }
   if($key==='testimoni' && ((int)($values['rating']??0)<1 || (int)$values['rating']>5))throw new RuntimeException('Rating harus antara 1 sampai 5.');
   $selectedRegistrations=[];
   if($key==='jadwal'){
    if((int)($values['kapasitas']??0)<1)throw new RuntimeException('Kapasitas jadwal minimal 1.');
    if(($values['waktu_selesai']??'')<=($values['waktu_mulai']??''))throw new RuntimeException('Waktu selesai harus setelah waktu mulai.');
    $selectedRegistrations=array_values(array_unique(array_filter(array_map('strval',$_POST['peserta_pendaftaran']??[]))));
    if(count($selectedRegistrations)>(int)$values['kapasitas'])throw new RuntimeException('Jumlah peserta melebihi kapasitas jadwal.');
    if($selectedRegistrations){
     $marks=implode(',',array_fill(0,count($selectedRegistrations),'?'));
     $sql="SELECT id_pendaftaran FROM pendaftaran WHERE id_program=? AND status_pendaftaran='disetujui' AND (id_jadwal IS NULL OR id_jadwal=?) AND id_pendaftaran IN ($marks)";
     $st=$db->prepare($sql);$types='ss'.str_repeat('s',count($selectedRegistrations));$params=array_merge([$values['id_program'],(string)($_POST['id']??'')],$selectedRegistrations);$st->bind_param($types,...$params);$st->execute();$valid=$st->get_result()->fetch_all(MYSQLI_ASSOC);$st->close();
     if(count($valid)!==count($selectedRegistrations))throw new RuntimeException('Ada peserta yang tidak sesuai program, belum disetujui, atau sudah memiliki jadwal lain.');
    }
   }
   if($key==='pendaftaran'){
    $isActive=in_array($values['status_pendaftaran']??'menunggu',['menunggu','disetujui','selesai'],true);
    $jid=$values['id_jadwal']??null;
    $exclude=(string)($_POST['id']??'');
    if($jid){
     $q=$db->prepare("SELECT id_program,kapasitas,status_jadwal FROM jadwal WHERE id_jadwal=? LIMIT 1");
     $q->bind_param('s',$jid);$q->execute();$jr=$q->get_result()->fetch_assoc();$q->close();
     if(!$jr||$jr['id_program']!==($values['id_program']??''))throw new RuntimeException('Jadwal harus berasal dari program yang dipilih.');
     if($isActive && in_array($jr['status_jadwal'],['Dibatalkan','Selesai'],true))throw new RuntimeException('Jadwal tidak dapat dipilih.');
     if($isActive){
      $q=$db->prepare("SELECT COUNT(*) total FROM pendaftaran WHERE id_jadwal=? AND status_pendaftaran IN ('menunggu','disetujui','selesai') AND id_pendaftaran<>?");
      $q->bind_param('ss',$jid,$exclude);$q->execute();$used=(int)$q->get_result()->fetch_assoc()['total'];$q->close();
      if($used >= (int)$jr['kapasitas'])throw new RuntimeException('Kapasitas jadwal sudah penuh.');
     }
    }
    if($isActive){
     $q=$db->prepare("SELECT kuota FROM program WHERE id_program=? LIMIT 1");
     $q->bind_param('s',$values['id_program']);$q->execute();$programRow=$q->get_result()->fetch_assoc();$q->close();
     $q=$db->prepare("SELECT COUNT(*) total FROM pendaftaran WHERE id_program=? AND status_pendaftaran IN ('menunggu','disetujui','selesai') AND id_pendaftaran<>?");
     $q->bind_param('ss',$values['id_program'],$exclude);$q->execute();$programUsed=(int)$q->get_result()->fetch_assoc()['total'];$q->close();
     if(!$programRow || $programUsed >= (int)$programRow['kuota'])throw new RuntimeException('Kuota program sudah penuh.');
     $q=$db->prepare("SELECT COUNT(*) total FROM pendaftaran WHERE id_peserta=? AND id_program=? AND status_pendaftaran IN ('menunggu','disetujui') AND id_pendaftaran<>?");
     $q->bind_param('sss',$values['id_peserta'],$values['id_program'],$exclude);$q->execute();$dup=(int)$q->get_result()->fetch_assoc()['total'];$q->close();
     if($dup>0)throw new RuntimeException('Peserta sudah memiliki pendaftaran aktif pada program tersebut.');
    }
   }
   if($key==='pembayaran'){
    $q=$db->prepare("SELECT d.status_pendaftaran,p.biaya FROM pendaftaran d JOIN program p ON p.id_program=d.id_program WHERE d.id_pendaftaran=? LIMIT 1");
    $pid=$values['id_pendaftaran'];$q->bind_param('s',$pid);$q->execute();$pr=$q->get_result()->fetch_assoc();$q->close();
    if(!$pr||$pr['status_pendaftaran']!=='disetujui')throw new RuntimeException('Pembayaran hanya dapat dicatat untuk pendaftaran yang disetujui.');
    if((float)$values['jumlah_pembayaran']!==(float)$pr['biaya'])throw new RuntimeException('Jumlah pembayaran harus sama persis dengan biaya program.');
    $excludeId=(string)($_POST['id']??'');
    $q=$db->prepare("SELECT id_pembayaran,status_pembayaran FROM pembayaran WHERE id_pendaftaran=? AND status_pembayaran IN ('menunggu','terverifikasi') AND id_pembayaran<>? LIMIT 1");
    $q->bind_param('ss',$pid,$excludeId);$q->execute();$active=$q->get_result()->fetch_assoc();$q->close();
    if($active)throw new RuntimeException('Pendaftaran sudah memiliki pembayaran aktif atau lunas.');
    if(($values['status_pembayaran']??'menunggu')==='terverifikasi'){
     $values['tanggal_verifikasi']=date('Y-m-d H:i:s');
     $values['id_admin']=$u['id'];
    }
   }
   if($key==='kehadiran'){
    $q=$db->prepare("SELECT d.id_peserta,d.id_program,j.id_program AS jadwal_program,j.tanggal_latihan FROM pendaftaran d JOIN jadwal j ON j.id_jadwal=? WHERE d.id_pendaftaran=? LIMIT 1");
    $q->bind_param('ss',$values['id_jadwal'],$values['id_pendaftaran']);$q->execute();$kr=$q->get_result()->fetch_assoc();$q->close();
    if(!$kr||$kr['id_peserta']!==$values['id_peserta']||$kr['id_program']!==$kr['jadwal_program'])throw new RuntimeException('Peserta, pendaftaran, dan jadwal tidak saling sesuai.');
    if($kr['tanggal_latihan']>date('Y-m-d'))throw new RuntimeException('Kehadiran belum dapat dicatat sebelum tanggal latihan.');
   }
   $db->begin_transaction();
   try{
    if($action==='create'){
     $id=new_id(strtoupper(substr($key,0,3)));$values=[$pk=>$id]+$values;$cols=array_keys($values);$sql="INSERT INTO `$table` (`".implode('`,`',$cols)."`) VALUES (".implode(',',array_fill(0,count($cols),'?')).")";$st=$db->prepare($sql);$types=str_repeat('s',count($cols));$vals=array_values($values);$st->bind_param($types,...$vals);$st->execute();$st->close();
    }else{
     $id=(string)($_POST['id']??'');if($id==='')throw new RuntimeException('ID data tidak valid.');
     $cols=array_keys($values);$set=implode(',',array_map(fn($x)=>"`$x`=?", $cols));$sql="UPDATE `$table` SET $set WHERE `$pk`=?";$st=$db->prepare($sql);$types=str_repeat('s',count($cols)+1);$vals=array_values($values);$vals[]=$id;$st->bind_param($types,...$vals);$st->execute();$st->close();
    }
    if($key==='jadwal'){
     $clear=$db->prepare("UPDATE pendaftaran SET id_jadwal=NULL WHERE id_jadwal=? AND status_pendaftaran='disetujui'");$clear->bind_param('s',$id);$clear->execute();$clear->close();
     if($selectedRegistrations){
      $marks=implode(',',array_fill(0,count($selectedRegistrations),'?'));
      $sql="UPDATE pendaftaran SET id_jadwal=? WHERE id_program=? AND status_pendaftaran='disetujui' AND id_jadwal IS NULL AND id_pendaftaran IN ($marks)";
      $st=$db->prepare($sql);$types='ss'.str_repeat('s',count($selectedRegistrations));$params=array_merge([$id,$values['id_program']],$selectedRegistrations);$st->bind_param($types,...$params);$st->execute();
      if($st->affected_rows!==count($selectedRegistrations))throw new RuntimeException('Penetapan peserta gagal. Muat ulang halaman dan coba kembali.');
      $st->close();
     }
    }
    $db->commit();
   }catch(Throwable $saveError){$db->rollback();throw $saveError;}
   if($action==='update'&&!empty($newImage)&&!empty($oldImage)&&$oldImage!==$newImage)delete_profile_image($oldImage);
   flash($c['title'].($action==='create'?' berhasil ditambahkan.':' berhasil diperbarui.'));
  }elseif($action==='delete'){
   $id=(string)($_POST['id']??'');
   $imageField=$key==='peserta'?'foto_profil':($key==='pelatih'?'foto':null);$oldImage=null;
   if($imageField!==null){$q=$db->prepare("SELECT `$imageField` FROM `$table` WHERE `$pk`=? LIMIT 1");$q->bind_param('s',$id);$q->execute();$row=$q->get_result()->fetch_assoc();$q->close();$oldImage=$row[$imageField]??null;}
   $st=$db->prepare("DELETE FROM `$table` WHERE `$pk`=?");$st->bind_param('s',$id);$st->execute();$st->close();
   if($imageField!==null)delete_profile_image($oldImage);
   flash('Data berhasil dihapus.');
  }
 }catch(Throwable $e){flash($e instanceof mysqli_sql_exception?'Data gagal diproses. Periksa relasi/duplikasi data dan pastikan data terkait tidak sedang digunakan.':$e->getMessage(),'error');}
 header('Location: '.$key.'.php');exit;
}
if($key==='jadwal'){
 $rows=$db->query("SELECT j.*,pr.nama_program,pl.nama_pelatih,(SELECT GROUP_CONCAT(ps.nama_lengkap ORDER BY ps.nama_lengkap SEPARATOR ', ') FROM pendaftaran pd JOIN peserta ps ON ps.id_peserta=pd.id_peserta WHERE pd.id_jadwal=j.id_jadwal AND pd.status_pendaftaran='disetujui') AS peserta_terdaftar FROM jadwal j LEFT JOIN program pr ON pr.id_program=j.id_program LEFT JOIN pelatih pl ON pl.id_pelatih=j.id_pelatih ORDER BY j.tanggal_latihan DESC,j.waktu_mulai DESC LIMIT 250");
}else{
 $rows=$db->query("SELECT * FROM `$table` ORDER BY `$pk` DESC LIMIT 250");
}
$displayCols=array_slice(array_keys($fields),0,6);
if($key==='pembayaran')$displayCols=['id_pendaftaran','kode_transaksi','jumlah_pembayaran','metode_pembayaran','status_pembayaran','bukti_pembayaran'];
if($key==='jadwal')$displayCols=['nama_program','nama_pelatih','peserta_terdaftar','tanggal_latihan','waktu_mulai','waktu_selesai','lokasi','status_jadwal'];
admin_header('Kelola '.$c['title'],$u);
?>
<section class="panel"><h2>Tambah <?=$c['title']?></h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="create"><div class="form-grid"><?php foreach($fields as $name=>$spec)render_field($db,$name,$spec,null);?></div><?php if($key==='jadwal')render_schedule_participants($db);?><button class="btn btn-gold">+ Simpan Data</button></form></section>
<section class="panel"><h2>Daftar <?=$c['title']?></h2><div class="table-wrap"><table><thead><tr><th>ID</th><?php foreach($displayCols as $col):?><th><?=e(ucwords(str_replace('_',' ',$col)))?></th><?php endforeach;?><th>Aksi</th></tr></thead><tbody>
<?php if(!$rows->num_rows):?><tr><td colspan="<?=count($displayCols)+2?>" class="empty">Belum ada data.</td></tr><?php endif;?>
<?php while($r=$rows->fetch_assoc()):?><tr><td><?=e($r[$pk])?></td><?php foreach($displayCols as $col):?><td><?php if($key==='pembayaran'&&$col==='bukti_pembayaran'&&!empty($r[$col])):?><a href="bukti_pembayaran.php?id=<?=rawurlencode($r[$pk])?>" target="_blank" rel="noopener">Lihat bukti</a><?php else:?><?=e(mb_strimwidth((string)($r[$col]??'-'),0,70,'…'))?><?php endif;?></td><?php endforeach;?><td><details><summary class="btn secondary">Edit</summary><form method="post" enctype="multipart/form-data" class="edit-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=e($r[$pk])?>"><div class="form-grid"><?php foreach($fields as $name=>$spec)render_field($db,$name,$spec,$r[$name]??null);?></div><?php if($key==='jadwal')render_schedule_participants($db,(string)$r['id_program'],(string)$r['id_jadwal']);?><button class="btn">Simpan Perubahan</button></form></details><form method="post" class="inline-form" onsubmit="return confirm('Hapus data ini?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($r[$pk])?>"><button class="btn danger">Hapus</button></form></td></tr><?php endwhile;?></tbody></table></div></section>
<?php if($key==='jadwal'): ?><script src="../assets/js/jadwal-peserta.js"></script><?php endif; ?>
<?php admin_footer(); ?>
