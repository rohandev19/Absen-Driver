# Use Case Diagram Sistem Absensi dan Manajemen Armada

Dokumen ini berisi bahan use case untuk skripsi. Diagram dibuat terpisah agar mudah digambar ulang secara manual atau dirender dengan Mermaid.

## Aktor Sistem

| Aktor | Peran |
|---|---|
| Driver | Menggunakan aplikasi mobile untuk absensi, laporan, transport cost, dan QR scan |
| Master Admin | Mengelola seluruh data utama, monitoring absensi, maintenance, laporan, dan user |
| Service Admin | Memproses service report, maintenance, dan approval operasional |
| Customer | Melihat data kendaraan dan melakukan approval laporan service |
| Scheduler | Menjalankan proses otomatis seperti alert, jadwal maintenance, dan reminder |

## 1. Use Case Diagram Driver dan Customer

```mermaid
flowchart LR
    Driver((Driver))
    Customer((Customer))

    subgraph DriverUseCase[Use Case Driver]
        UC1[Login Mobile]
        UC2[Check-In]
        UC3[Check-Out]
        UC4[Check-Out Offline]
        UC5[Sinkronisasi Offline]
        UC6[Lihat Riwayat Absensi]
        UC7[Scan QR Kendaraan]
        UC8[Lapor Darurat]
        UC9[Buat Service Report]
        UC10[Ajukan Transport Cost]
        UC11[Lihat Panduan Driver]
        UC12[Ganti Password]
    end

    subgraph CustomerUseCase[Use Case Customer]
        UC13[Login Customer]
        UC14[Lihat Dashboard Customer]
        UC15[Lihat Kendaraan]
        UC16[Lihat Riwayat Maintenance]
        UC17[Review Service Report]
        UC18[Approve Service Report]
        UC19[Tanda Tangan Dokumen]
        UC20[Download Dokumen]
    end

    Driver --- UC1
    Driver --- UC2
    Driver --- UC3
    Driver --- UC4
    Driver --- UC5
    Driver --- UC6
    Driver --- UC7
    Driver --- UC8
    Driver --- UC9
    Driver --- UC10
    Driver --- UC11
    Driver --- UC12

    Customer --- UC13
    Customer --- UC14
    Customer --- UC15
    Customer --- UC16
    Customer --- UC17
    Customer --- UC18
    Customer --- UC19
    Customer --- UC20

    UC4 -. include .-> UC5
    UC18 -. include .-> UC19
    UC18 -. include .-> UC20
```

## 2. Use Case Diagram Admin dan Scheduler

```mermaid
flowchart LR
    Master((Master Admin))
    Service((Service Admin))
    Scheduler((Scheduler))

    subgraph AdminUseCase[Use Case Admin]
        UC1[Login Web]
        UC2[Lihat Dashboard Admin]
        UC3[Kelola Driver]
        UC4[Kelola Kendaraan]
        UC5[Kelola Project]
        UC6[Kelola Customer]
        UC7[Kelola User]
        UC8[Monitoring Absensi]
        UC9[Koreksi KM]
        UC10[Export Laporan]
        UC11[Kelola Komponen Kendaraan]
        UC12[Kelola Alert Maintenance]
        UC13[Kelola Jadwal Maintenance]
        UC14[Selesaikan Maintenance]
        UC15[Generate PDF Finance Maintenance]
        UC16[Proses Service Report]
        UC17[Approve atau Reject Service Report]
        UC18[Kelola Laporan Darurat]
        UC19[Approve Transport Cost]
        UC20[Submit Transport Cost ke Finance]
        UC21[Kelola QR Code]
    end

    subgraph SchedulerUseCase[Use Case Scheduler]
        UC22[Update Status Komponen]
        UC23[Generate Maintenance Alert]
        UC24[Generate Jadwal Preventive]
        UC25[Kirim Reminder 8 Jam]
        UC26[Kirim Reminder SIM]
    end

    Master --- UC1
    Master --- UC2
    Master --- UC3
    Master --- UC4
    Master --- UC5
    Master --- UC6
    Master --- UC7
    Master --- UC8
    Master --- UC9
    Master --- UC10
    Master --- UC11
    Master --- UC12
    Master --- UC13
    Master --- UC14
    Master --- UC15
    Master --- UC16
    Master --- UC17
    Master --- UC18
    Master --- UC19
    Master --- UC20
    Master --- UC21

    Service --- UC1
    Service --- UC2
    Service --- UC8
    Service --- UC10
    Service --- UC11
    Service --- UC12
    Service --- UC13
    Service --- UC14
    Service --- UC15
    Service --- UC16
    Service --- UC17
    Service --- UC18

    Scheduler --- UC22
    Scheduler --- UC23
    Scheduler --- UC24
    Scheduler --- UC25
    Scheduler --- UC26

    UC14 -. include .-> UC15
    UC23 -. extend .-> UC12
    UC24 -. extend .-> UC13
```

## 3. Deskripsi Use Case Utama

| No | Use Case | Aktor | Deskripsi Singkat |
|---|---|---|---|
| 1 | Login Mobile | Driver | Driver masuk ke aplikasi menggunakan ID driver dan password |
| 2 | Check-In | Driver | Driver memulai tugas dengan input kendaraan, GPS, KM awal, selfie, dan foto speedometer |
| 3 | Check-Out | Driver | Driver mengakhiri tugas dengan input KM akhir, checklist kendaraan, catatan, dan foto speedometer |
| 4 | Check-Out Offline | Driver | Driver menyimpan data check-out di perangkat saat tidak ada koneksi |
| 5 | Sinkronisasi Offline | Driver | Sistem mengirim data offline ke server saat koneksi tersedia |
| 6 | Lapor Darurat | Driver | Driver membuat laporan darurat terkait kendaraan atau kondisi operasional |
| 7 | Buat Service Report | Driver | Driver membuat laporan kerusakan atau service kendaraan |
| 8 | Ajukan Transport Cost | Driver | Driver mengajukan biaya perjalanan berdasarkan attendance |
| 9 | Review Service Report | Customer | Customer memeriksa laporan service yang membutuhkan persetujuan |
| 10 | Approve Service Report | Customer | Customer menyetujui laporan service dan menandatangani dokumen |
| 11 | Kelola Driver | Master Admin | Admin menambah, mengubah, dan menghapus data driver |
| 12 | Kelola Kendaraan | Master Admin | Admin mengelola data aset kendaraan dan status unit |
| 13 | Monitoring Absensi | Master Admin, Service Admin | Admin melihat riwayat absensi, check-in, check-out, dan kondisi kendaraan |
| 14 | Kelola Komponen Kendaraan | Master Admin, Service Admin | Admin mengatur komponen kendaraan untuk preventive maintenance |
| 15 | Kelola Alert Maintenance | Master Admin, Service Admin | Admin melihat, acknowledge, dan resolve alert maintenance |
| 16 | Kelola Jadwal Maintenance | Master Admin, Service Admin | Admin membuat dan memantau jadwal preventive/corrective/predictive maintenance |
| 17 | Selesaikan Maintenance | Master Admin, Service Admin | Admin menyelesaikan jadwal maintenance, upload bukti, dan memperbarui data komponen |
| 18 | Proses Service Report | Master Admin, Service Admin | Admin memeriksa laporan service dan meneruskan ke approval |
| 19 | Approve Transport Cost | Master Admin | Admin menyetujui atau menolak pengajuan biaya transport |
| 20 | Generate Maintenance Alert | Scheduler | Sistem otomatis membuat alert jika komponen warning, critical, atau overdue |
| 21 | Generate Jadwal Preventive | Scheduler | Sistem otomatis membuat jadwal preventive untuk komponen yang membutuhkan maintenance |

## 4. Bahan Sketsa Manual

Jika digambar manual untuk skripsi, gunakan pembagian berikut.

### Sketsa 1 - Driver dan Customer

```text
                 +------------------------------+
                 |        SISTEM ABSENSI        |
                 |                              |
Driver --------- | Login Mobile                 |
Driver --------- | Check-In                     |
Driver --------- | Check-Out                    |
Driver --------- | Check-Out Offline            |
Driver --------- | Sinkronisasi Offline         |
Driver --------- | Lihat Riwayat Absensi        |
Driver --------- | Scan QR Kendaraan            |
Driver --------- | Lapor Darurat                |
Driver --------- | Buat Service Report          |
Driver --------- | Ajukan Transport Cost        |
                 |                              |
Customer ------- | Login Customer               |
Customer ------- | Lihat Dashboard Customer     |
Customer ------- | Review Service Report        |
Customer ------- | Approve Service Report       |
Customer ------- | Tanda Tangan Dokumen         |
                 +------------------------------+
```

### Sketsa 2 - Admin dan Scheduler

```text
                 +--------------------------------+
                 |         SISTEM ADMIN           |
                 |                                |
Master Admin --- | Kelola Driver                  |
Master Admin --- | Kelola Kendaraan               |
Master Admin --- | Kelola Project                 |
Master Admin --- | Kelola Customer                |
Master Admin --- | Kelola User                    |
Master Admin --- | Monitoring Absensi             |
Master Admin --- | Export Laporan                 |
Master Admin --- | Kelola Maintenance             |
Master Admin --- | Proses Service Report          |
Master Admin --- | Approve Transport Cost         |
                 |                                |
Service Admin -- | Kelola Maintenance             |
Service Admin -- | Proses Service Report          |
Service Admin -- | Kelola Laporan Darurat         |
                 |                                |
Scheduler ------ | Update Status Komponen         |
Scheduler ------ | Generate Maintenance Alert     |
Scheduler ------ | Generate Jadwal Preventive     |
Scheduler ------ | Kirim Reminder                 |
                 +--------------------------------+
```

## Catatan

- Untuk skripsi, cukup tampilkan use case yang benar-benar penting dan mudah dijelaskan.
- Use case detail seperti upload foto, validasi GPS, dan optimasi gambar tidak perlu dijadikan use case utama; itu cukup dijelaskan di activity atau sequence diagram.
- Jika diagram terlalu penuh, gunakan dua gambar: `Use Case Driver-Customer` dan `Use Case Admin-Scheduler`.
