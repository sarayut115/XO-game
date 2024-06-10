XO Game เขียนโปรแกรมด้วยภาษา PHP,HTML,CSS โดยมี MySQL เป็นฐานข้อมูล

#วิธีการ setup และ run โปรแกรม

1. ติดตั้ง AppServ บนเครื่องของคุณ AppServ เป็นแอปพลิเคชันบน Windows ที่รวม Apache, MySQL และ PHP ดาวน์โหลดได้ที่ https://www.appserv.org/th/

2. ซึ่งในขั้นตอนการตั้งรหัสผ่าน ให้ใส่ user:root และ password:123456789 (หากใส่ไม่ตรงกัน ต้องไปแก้ในโค้ด db.php)

3. สร้างฐานข้อมูลใหม่:
  - เปิด phpMyAdmin ในเบราว์เซอร์ของคุณโดยไปที่ URL: http://localhost/phpmyadmin
  - เข้าสู่ระบบด้วยชื่อผู้ใช้และรหัสผ่านของ MySQL ที่ตั้งไว้ user:root และ password:123456789
  - คลิกที่ "Database" ที่แถบเมนูด้านบน
  - ในช่อง "สร้างฐานข้อมูลใหม่" ใส่ชื่อ "xo_game"
  - เลือก "utf8_general_ci"

4. เมื่อฐานข้อมูล xo_game ถูกสร้างแล้ว คลิกที่ชื่อฐานข้อมูลในแถบเมนูด้านซ้าย
  - เลือกแท็บ "นำเข้า" ด้านบน
  - กดปุ่ม "เลือกไฟล์" เพื่อเลือกไฟล์ db.sql ที่คุณต้องการนำเข้า
  - หลังจากนั้นคลิกที่ "รัน" เพื่อเริ่มกระบวนการ import

5. หลังจากนั้นสามารถเข้าเล่นเกมได้เลย ที่ http://localhost/


#วิธีออกแบบโปรแกรมและ algorithm ที่ใช้

1. การออกแบบฐานข้อมูล:
  - games: เก็บข้อมูลเกมที่กำลังเล่น รวมถึงข้อมูลเกี่ยวกับผู้เล่นและผลลัพธ์ของเกม เช่น ID เกม, ชื่อผู้เล่น 1 และ 2, ขนาดของกระดาน, ผู้ชนะ
  - moves: เก็บข้อมูลการเคลื่อนไหวของผู้เล่นในแต่ละรอบ เช่น ตำแหน่งที่เคลื่อนไหว, ผู้เล่นที่เคลื่อนไหว
  - game_history: เก็บประวัติการเล่นของเกมแต่ละรอบ เพื่อการเรียกดูหรือการเก็บบันทึก

2. เริ่มต้นเกม:
  - สร้างกระดานเปล่าโดยมีขนาดตามที่ผู้เล่นเลือก
  - Player แต่ละคนสามารถตั้งชื่อของตัวเองได้ ยกเว้นตั้งชื่อBot
  - กำหนดผู้เล่นในเกม (Player 1 และ Player 2 หรือ Player 1 และ Bot) และกำหนดผู้เริ่มเล่นคนแรกเป็น X

3. รับ Input จากผู้เล่น:
  - ตรวจสอบว่าตำแหน่งที่ผู้เล่นต้องการวางเครื่องหมาย X หรือ O ยังว่างหรือไม่
  - บันทึกการเลือกตำแหน่งลงในกระดาน

4. ตรวจสอบสถานะเกม:
  - ตรวจสอบว่ามีผู้ชนะหรือยัง โดยตรวจสอบแนวนอน แนวตั้ง และแนวทแยง
  - ตรวจสอบว่ากระดานเต็มและไม่มีผู้ชนะ (เสมอ)

5. สลับผู้เล่น:
  - เมื่อผู้เล่นทำการคลิกเลือกช่องเสร็จสิ้น จะสลับผู้เล่นให้เป็นคนต่อไป

6. ผลลัพธ์เกม:
  - แสดงผลลัพธ์ของการเล่นเกมที่เกิดขึ้น เช่น ผู้ชนะหรือเสมอ
  - แสดงประวัติการเล่นเกมเมื่อผู้เล่นต้องการดูหรือเล่นซ้ำ

7. การเล่นกับ Bot เมื่อผู้เล่นต้องการเล่นคนเดียว:
  - เขียนโค้ดการเลือกตำแหน่งในการวางเครื่องหมาย O ของ Bot บนกระดานโดยจะเลือกวางช่องที่ยังว่างอยู่เท่านั้น

8. ฐานข้อมูล:
  - ใช้ฐานข้อมูลเพื่อเก็บประวัติการเล่นเกม เช่น ผู้เล่น ตำแหน่งช่องที่เลือก และเวลาที่ทำรายการ

9. ฟังก์ชันเสริม:
  - เพิ่มฟังก์ชันพิเศษ เช่น การเริ่มเกมใหม่หรือเล่นซ้ำ
