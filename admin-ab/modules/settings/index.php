<?php
require_once __DIR__.'/../../../config/database.php';
require_once __DIR__.'/../../../config/app.php';
initSession(); requireLogin();
$pageTitle='Pengaturan'; $pageSubtitle='Konfigurasi website perusahaan';
$errors=[];
$profile = Database::fetchOne("SELECT * FROM company_profile LIMIT 1") ?: [];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrf()){$errors[]='Token tidak valid.';}
    else {
        $tab = post('tab','company');

        if ($tab === 'company') {
            $data = [
                'company_name'    => post('company_name'),
                'tagline'         => post('tagline'),
                'description'     => post('description'),
                'address'         => post('address'),
                'city'            => post('city'),
                'province'        => post('province'),
                'postal_code'     => post('postal_code'),
                'phone'           => post('phone'),
                'whatsapp'        => post('whatsapp'),
                'email'           => post('email'),
                'website'         => post('website'),
                'vision'          => post('vision'),
                'mission'         => post('mission'),
                'established_year'=> postInt('established_year'),
                'total_employees' => postInt('total_employees'),
                'social_facebook' => post('social_facebook'),
                'social_instagram'=> post('social_instagram'),
                'social_youtube'  => post('social_youtube'),
                'social_tiktok'   => post('social_tiktok'),
                'maps_embed'      => post('maps_embed'),
                'meta_title'      => post('meta_title'),
                'meta_description'=> post('meta_description'),
            ];

            if (!empty($_FILES['logo']['name'])) {
                $up = uploadImage($_FILES['logo'],'company');
                if ($up['success']) {
                    if (!empty($profile['logo'])) deleteFile($profile['logo']);
                    $data['logo'] = $up['filename'];
                } else { $errors[] = $up['message']; }
            }

            if (empty($errors)) {
                if ($profile) { Database::update('company_profile',$data,'id=?',[$profile['id']]); }
                else { Database::insert('company_profile',$data); }
                logActivity('update','settings','Memperbarui profil perusahaan');
                setFlash('success','Pengaturan berhasil disimpan.');
                redirect(APP_URL.'/admin-ab/modules/settings/index.php');
            }
        }

        if ($tab === 'password') {
            $oldPass = $_POST['old_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $conPass = $_POST['confirm_password'] ?? '';
            $user = currentUser();
            $dbUser = Database::fetchOne("SELECT * FROM users WHERE id=?",[$user['id']]);
            if (!password_verify($oldPass,$dbUser['password'])) $errors[]='Password lama tidak sesuai.';
            if (strlen($newPass)<6) $errors[]='Password baru minimal 6 karakter.';
            if ($newPass!==$conPass) $errors[]='Konfirmasi password tidak cocok.';
            if (empty($errors)) {
                Database::update('users',['password'=>password_hash($newPass,PASSWORD_DEFAULT)],'id=?',[$user['id']]);
                logActivity('update','settings','Mengubah password');
                setFlash('success','Password berhasil diubah.');
                redirect(APP_URL.'/admin-ab/modules/settings/index.php?tab=password');
            }
        }
    }
}

$profile = Database::fetchOne("SELECT * FROM company_profile LIMIT 1") ?: [];
$activeTab = get('tab','company');
include __DIR__.'/../../partials/head.php';
?>
<div class="admin-wrapper">
<?php include __DIR__.'/../../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__.'/../../partials/topbar.php'; ?>
<div class="page-body">

<div class="section-header mb-20"><h2>Pengaturan Sistem</h2><p>Kelola informasi perusahaan dan konfigurasi website</p></div>

<?php foreach($errors as $e):?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?=htmlspecialchars($e)?></div><?php endforeach;?>

<!-- Tabs -->
<div style="display:flex;gap:6px;margin-bottom:20px;border-bottom:1.5px solid var(--border);padding-bottom:0;">
  <?php foreach(['company'=>'🏢 Profil Perusahaan','password'=>'🔒 Ganti Password'] as $tab=>$lbl):?>
  <a href="?tab=<?=$tab?>" style="padding:10px 18px;border-radius:var(--radius-md) var(--radius-md) 0 0;font-size:13.5px;font-weight:600;text-decoration:none;border:1.5px solid <?=$activeTab===$tab?'var(--border) var(--border) var(--bg-surface)':'transparent'?>;background:<?=$activeTab===$tab?'var(--bg-surface)':'transparent'?>;color:<?=$activeTab===$tab?'var(--brand-600)':'var(--text-secondary)'?>;margin-bottom:-1.5px;"><?=$lbl?></a>
  <?php endforeach;?>
</div>

<?php if($activeTab==='company'):?>
<form method="POST" enctype="multipart/form-data">
  <?=csrfField()?><input type="hidden" name="tab" value="company">
  <div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;">
    <div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Identitas Perusahaan</div></div>
        <div class="card-body">
          <div class="form-group"><label class="form-label">Nama Perusahaan <span>*</span></label><input type="text" name="company_name" class="form-control" value="<?=htmlspecialchars($profile['company_name']??'')?>" required></div>
          <div class="form-group"><label class="form-label">Tagline</label><input type="text" name="tagline" class="form-control" value="<?=htmlspecialchars($profile['tagline']??'')?>"></div>
          <div class="form-group"><label class="form-label">Deskripsi Perusahaan</label><textarea name="description" class="form-control" rows="4"><?=htmlspecialchars($profile['description']??'')?></textarea></div>
          <div class="grid grid-2">
            <div class="form-group"><label class="form-label">Tahun Berdiri</label><input type="number" name="established_year" class="form-control" value="<?=$profile['established_year']??2010?>"></div>
            <div class="form-group"><label class="form-label">Jumlah Karyawan</label><input type="number" name="total_employees" class="form-control" value="<?=$profile['total_employees']??50?>"></div>
          </div>
          <div class="form-group"><label class="form-label">Visi</label><textarea name="vision" class="form-control" rows="3"><?=htmlspecialchars($profile['vision']??'')?></textarea></div>
          <div class="form-group"><label class="form-label">Misi</label><textarea name="mission" class="form-control" rows="5"><?=htmlspecialchars($profile['mission']??'')?></textarea></div>
        </div>
      </div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Kontak & Alamat</div></div>
        <div class="card-body">
          <div class="form-group"><label class="form-label">Alamat Lengkap</label><textarea name="address" class="form-control" rows="3"><?=htmlspecialchars($profile['address']??'')?></textarea></div>
          <div class="grid grid-2">
            <div class="form-group"><label class="form-label">Kota</label><input type="text" name="city" class="form-control" value="<?=htmlspecialchars($profile['city']??'')?>"></div>
            <div class="form-group"><label class="form-label">Provinsi</label><input type="text" name="province" class="form-control" value="<?=htmlspecialchars($profile['province']??'')?>"></div>
          </div>
          <div class="grid grid-2">
            <div class="form-group"><label class="form-label">Nomor Telepon</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($profile['phone']??'')?>"></div>
            <div class="form-group"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-control" value="<?=htmlspecialchars($profile['whatsapp']??'')?>"></div>
          </div>
          <div class="grid grid-2">
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($profile['email']??'')?>"></div>
            <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="<?=htmlspecialchars($profile['website']??'')?>"></div>
          </div>
          <div class="form-group"><label class="form-label">Google Maps Embed URL</label><input type="text" name="maps_embed" class="form-control" value="<?=htmlspecialchars($profile['maps_embed']??'')?>" placeholder="https://maps.google.com/maps?..."></div>
        </div>
      </div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Media Sosial</div></div>
        <div class="card-body">
          <div class="form-group"><label class="form-label"><i class="fab fa-facebook" style="color:#1877f2;"></i> Facebook</label><input type="url" name="social_facebook" class="form-control" value="<?=htmlspecialchars($profile['social_facebook']??'')?>"></div>
          <div class="form-group"><label class="form-label"><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram</label><input type="url" name="social_instagram" class="form-control" value="<?=htmlspecialchars($profile['social_instagram']??'')?>"></div>
          <div class="form-group"><label class="form-label"><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube</label><input type="url" name="social_youtube" class="form-control" value="<?=htmlspecialchars($profile['social_youtube']??'')?>"></div>
         <div class="form-group">
    <label class="form-label"><i class="fab fa-tiktok" style="color:#000;"></i> TikTok</label>
    <input type="url" name="social_tiktok" class="form-control" 
           value="<?= htmlspecialchars($profile['social_tiktok'] ?? '') ?>" 
           placeholder="https://www.tiktok.com/@username">
</div>
        </div>
      </div>
    </div>
    <div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">Logo Perusahaan</div></div>
        <div class="card-body">
          <?php if(!empty($profile['logo'])):?>
            <img id="logo-preview" src="<?=uploadUrl($profile['logo'])?>" style="max-width:160px;border-radius:8px;margin-bottom:12px;display:block;">
          <?php else:?>
            <img id="logo-preview" style="display:none;max-width:160px;border-radius:8px;margin-bottom:12px;">
          <?php endif;?>
          <div class="upload-area" onclick="document.getElementById('logo-input').click()">
            <i class="fas fa-image" style="font-size:22px;color:var(--text-muted);"></i>
            <div style="font-size:13px;margin-top:6px;"><?=!empty($profile['logo'])?'Ganti logo':'Upload logo'?></div>
          </div>
          <input type="file" id="logo-input" name="logo" accept="image/*" style="display:none;" data-preview-target="logo-preview">
        </div>
      </div>
      <div class="card mb-20">
        <div class="card-header"><div class="card-title">SEO</div></div>
        <div class="card-body">
          <div class="form-group"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?=htmlspecialchars($profile['meta_title']??'')?>"></div>
          <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="4"><?=htmlspecialchars($profile['meta_description']??'')?></textarea></div>
        </div>
      </div>
    </div>
  </div>
  <div style="margin-top:8px;"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Pengaturan</button></div>
</form>
<?php endif;?>

<?php if($activeTab==='password'):?>
<div class="card" style="max-width:480px;">
  <div class="card-header"><div class="card-title">Ganti Password</div></div>
  <div class="card-body">
    <form method="POST"><?=csrfField()?><input type="hidden" name="tab" value="password">
      <div class="form-group"><label class="form-label">Password Lama <span>*</span></label><input type="password" name="old_password" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Password Baru <span>*</span></label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
      <div class="form-group"><label class="form-label">Konfirmasi Password <span>*</span></label><input type="password" name="confirm_password" class="form-control" required></div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Ganti Password</button>
    </form>
  </div>
</div>
<?php endif;?>

</div>
<?php include __DIR__.'/../../partials/footer.php'; ?>
