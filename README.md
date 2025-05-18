# dormitory_automation 
# Yurt Otomasyon Sistemi - Ocean Breeze

Ocean Breeze, bir öğrenci yurdunun giriş-çıkış, izin ve ödeme süreçlerini web tabanlı olarak yöneten PHP/MySQL tabanlı bir uygulamadır.

## Özellikler           

* ### **Kimlik Doğrulama & Yetkilendirme**
  ![`security` ve `students_affair` rollerine özel güvenli giriş.](assets/images/security.png)
  ![`security` ve `students_affair` rollerine özel güvenli giriş.](assets/images/studaff_officer.png)
  
  *  -`security` ve `students_affair` rollerine özel güvenli giriş.

	
   

* ### **Giriş/Çıkış Takibi**           
  ![Çift giriş/çıkış önlenir; her işlem zaman damgasıyla kaydedilir.](assets/images/enter_leave.png)
  
  *  -Çift giriş/çıkış önlenir; her işlem zaman damgasıyla kaydedilir.



* ### **İzin Yönetimi**:            
  ![pers](assets/images/permission.png)
  ![pers](assets/images/perm_follw.png)
  ![pers](assets/images/total_perm.png)
  
  * -Öğrenciler izin talep edebilir; personel bekleyen talepleri onaylayabilir veya silebilir. 45 günden fazla izin kullanmış öğrenciler takip edilir.


* ### **Fatura & Ödeme Modülü**:     
  ![pay](assets/images/act_pay.png)
  ![pay](assets/images/pay.png)
  ![pay](assets/images/pay_follw.png)
  
  * -Öğrencilerin yurt ödemeleri takip edilir; aylık olarak fatura oluşturulur; yeni kayıtlarda o ayın faturası otomatik olarak fatura oluşturma.



* ### **Öğrenci ekleme**:
  ![add](assets/images/add_stud.png)
  
  *  -Yeni öğrenci kaydı yapılır.
 
* ### **Öğrenci listeleme**:
  ![add](assets/images/list_stud.png)
  
  *  -Kayıtlı olan öğrencilerin bilgileri görüntülenir.



* ### **Öğrenci oda takibi**:
  ![add](assets/images/beds.png)
  
  *  -Öğrenci yeni eklendiğinde oda ve yatak nosu atanır; isteğe göre oda değişikliği yapılır.


## Teknolojiler

* **Backend**: PHP (PDO)
* **Veritabanı**: MySQL 8.0
* **Frontend**: HTML, CSS,
* **IDE**: Visual Studio Code
* **Yerel Sunucu**: XAMPP (Apache + MySQL)
* **Versiyon Kontrol**: Git & GitHub

## Gereksinimler

* PHP 7.4 veya üzeri
* MySQL 8.0
* XAMPP (ya da benzer LAMP/WAMP)
* Git

## Kurulum

## Kurulum

1. **Veritabanı Oluşturun**  
   - phpMyAdmin’e giriş yapın.  
   - “Yeni” → veritabanı adı olarak `dormitory_database` yazıp **Oluştur**’a tıklayın.

2. **SQL Dosyalarını İçe Aktarın**  
   - Sol menüden `dormitory_database`’ı seçin.  
   - Üstteki **İçe Aktar**(**import**) sekmesine gidin.  
   - `dormitory_database.sql` dosyasını seçin ve **Git** e basın
   - Böylece tablo yapısı ve başlangıç verileriniz yüklenecek.

3. **Yapılandırma Dosyasını Güncelleyin**  
   - `config/db.php` içindeki şu satırları kendi ayarlarınıza göre düzenleyin:
     ```php
     $host = 'localhost';  
     $dbname = 'dormitory_database';
     $user = 'root';
     $pass = '';
     ```
   - Eğer MySQL port’unuz farklıysa `3307` gibi port numarasını da ekleyin:
     ```php
     $dsn = "mysql:host=$host;port=3307;dbname=$dbname;charset=utf8";
     ```

4. **Projeyi Çalıştırın**  
   - Tüm dosyalar XAMPP’in `htdocs/dormitory_automation/` klasörüne kopyalanmış olmalı.  
   - Tarayıcınızda `http://localhost/dormitory_automation/` adresine gidin.

5. **Varsayılan Kullanıcılar**  
   - **Güvenlik** rolüyle:  
     - TC: `12345678901`, şifre: `123456`  
   - **Öğrenci İşleri** rolüyle:  
     - TC: `12345678932`, şifre: `29sude`  


6. **Uygulamayı açın**:

   * Tarayıcıda `http://localhost/dormitory_automation/public/login.php` adresine gidin.
   * Her şey doğruysa uygulama giriş ekranını ve panelleri göreceksiniz!




## Proje Yapısı

```
├── public/                # Giriş ve dashboard sayfaları
├── config/                # Veritabanı bağlantı ayarları
│   └── db.php             # PDO ile bağlantı yapılandırması
├── permissions/           # İzin talepleri modülleri
├── payments/              # Fatura ve ödeme modülleri
├── students/              # Öğrenci kayıt ve yatak atama modülleri
├── security/              # Güvenlik kullanıcı sayfaları
└── assets/                # Görseller, stiller
```
