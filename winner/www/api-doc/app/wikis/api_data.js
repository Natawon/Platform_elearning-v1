define({ "api": [
  {
    "type": "post",
    "url": "/set/SETGroup/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "1_SET_Member",
    "description": "<p>Login to e-learning site for SET Member.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "1_SET_Member",
    "description": "<p>Login to e-learning site for SET Member.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "1_SET_Member",
    "description": "<p>Get course list for SET Member.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "1_SET_Member",
    "description": "<p>Get course list for SET Member.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "1_SET_Member",
    "description": "<p>Get course information for SET Member.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "1_SET_Member",
    "description": "<p>Get course information for SET Member.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "1_SET_Member",
    "description": "<p>Download e-cerfiticate file for SET Member.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETGroup/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "1_SET_Member",
    "description": "<p>Download e-cerfiticate file for SET Member.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.job_title",
            "description": "<p>อาชีพ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.income",
            "description": "<p>รายได้</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(255)",
            "optional": false,
            "field": "user_profile.address",
            "description": "<p>ที่อยู่</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.province",
            "description": "<p>จังหวัด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(8)",
            "optional": false,
            "field": "user_profile.zip_code",
            "description": "<p>รหัสไปรษณีย์</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "1_SET_Member",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "2_SET_Listed_Company",
    "description": "<p>Login to e-learning site for Listed Company.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "2_SET_Listed_Company",
    "description": "<p>Login to e-learning site for Listed Company.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "2_SET_Listed_Company",
    "description": "<p>Get course list for Listed Company.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "2_SET_Listed_Company",
    "description": "<p>Get course list for Listed Company.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "2_SET_Listed_Company",
    "description": "<p>Get course information for Listed Company.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "2_SET_Listed_Company",
    "description": "<p>Get course information for Listed Company.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "2_SET_Listed_Company",
    "description": "<p>Download e-cerfiticate file for Listed Company.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETListedCompany/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "2_SET_Listed_Company",
    "description": "<p>Download e-cerfiticate file for Listed Company.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference), ใช้เพื่อ link กับ SET Regis</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทจดทะเบียน เช่น BBL, KGI</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.role",
            "description": "<p>บทบาทในระบบต้นทาง</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "2_SET_Listed_Company",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "3_SET_Broker",
    "description": "<p>Login to e-learning site for Broker.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "3_SET_Broker",
    "description": "<p>Login to e-learning site for Broker.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "3_SET_Broker",
    "description": "<p>Get course list for Broker.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "3_SET_Broker",
    "description": "<p>Get course list for Broker.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "3_SET_Broker",
    "description": "<p>Get course information for Broker.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "3_SET_Broker",
    "description": "<p>Get course information for Broker.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "3_SET_Broker",
    "description": "<p>Download e-cerfiticate file for Broker.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETBroker/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "3_SET_Broker",
    "description": "<p>Download e-cerfiticate file for Broker.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสบริษัทหลักทรัพย์</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_type_id",
            "description": "<p>ประเภทใบอนุญาต<br>0 = ไม่มี<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.license_id",
            "description": "<p>เลขที่ใบอนุญาต</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน<br>1 = ผู้แนะนำการลงทุน IC<br>2 = นักวิเคราะห์การลงทุน IA<br>3 = Back office</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_degree_id",
            "description": "<p>วุฒิการศึกษา<br>1 = ต่ำกว่าปริญญาตรี<br>2 = ปริญญาตรี<br>3 = ปริญญาโท<br>4 = ปริญญาเอก</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "3_SET_Broker",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "4_SET_Student",
    "description": "<p>Login to e-learning site for Student.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "4_SET_Student",
    "description": "<p>Login to e-learning site for Student.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "4_SET_Student",
    "description": "<p>Get course list for Student.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "4_SET_Student",
    "description": "<p>Get course list for Student.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "4_SET_Student",
    "description": "<p>Get course information for Student.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "4_SET_Student",
    "description": "<p>Get course information for Student.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "4_SET_Student",
    "description": "<p>Download e-cerfiticate file for Student.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETStudent/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "4_SET_Student",
    "description": "<p>Download e-cerfiticate file for Student.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.name_title",
            "description": "<p>คำนำหน้าชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(1)",
            "optional": false,
            "field": "user_profile.gender",
            "description": "<p>เพศ<br>F = หญิง<br>M = ชาย</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.id_card",
            "description": "<p>เลขบัตรประชาชน</p>"
          },
          {
            "group": "Parameter",
            "type": "date",
            "optional": false,
            "field": "user_profile.birth_date",
            "description": "<p>วันเกิด</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(10)",
            "optional": false,
            "field": "user_profile.mobile_number",
            "description": "<p>หมายเลขโทรศัพท์มือถือ, Validate format</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.institution_id",
            "description": "<p>รหัสมหาวิทยาลัย<br>21 = มหาวิทยาลัยเกษตรศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.faculty_id",
            "description": "<p>รหัสคณะ<br>32 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสนักศึกษา</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.field_study_id",
            "description": "<p>รหัสสาขาวิชา<br>192 = คณะเศรษฐศาสตร์<br>...</p>"
          },
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": "user_profile.education_level_id",
            "description": "<p>ระดับการศึกษา<br>1 = ปริญญาตรี<br>2 = ปริญญาโท<br>...</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "4_SET_Student",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "5_SET_Employee",
    "description": "<p>Login to e-learning site for Employee.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/login",
    "title": "E1: Single Sign-on",
    "name": "E1__Single_Sign_on",
    "group": "5_SET_Employee",
    "description": "<p>Login to e-learning site for Employee.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "5_SET_Employee",
    "description": "<p>Get course list for Employee.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses",
    "title": "E2: Get Course List",
    "name": "E2__Get_Course_List",
    "group": "5_SET_Employee",
    "description": "<p>Get course list for Employee.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Object[]",
            "optional": false,
            "field": "courses",
            "description": "<p>รายการวิชาเรียน</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(3)",
            "optional": false,
            "field": "courses.id",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(50)",
            "optional": false,
            "field": "courses.code",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(255)",
            "optional": false,
            "field": "courses.title",
            "description": "<p>ค่ารหัสอ้างอิง Courses</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.thumbnail",
            "description": "<p>Path รูปภาพ Thumbnail</p>"
          },
          {
            "group": "Success 200",
            "type": "Int(7)",
            "optional": false,
            "field": "courses.price",
            "description": "<p>ราคาของ Course</p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.latest",
            "description": "<p><code>true = course ใหม่</code>, <code>false = ไม่ใช่ course ใหม่</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.recommended",
            "description": "<p><code>true = course แนะนำ</code>, <code>false = ไม่ใช่ course แนะนำ</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "courses.free",
            "description": "<p><code>true = course ฟรี</code>, <code>false = ไม่ใช่ course ฟรี</code></p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories",
            "description": "<p>กลุ่มของหลักสูตร</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.id",
            "description": "<p>ค่ารหัสอ้างอิง ของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.title",
            "description": "<p>ชื่อของกลุ่ม</p>"
          },
          {
            "group": "Success 200",
            "type": "Varchar(128)",
            "optional": false,
            "field": "courses.categories.hex_color",
            "description": "<p>ค่าสีของกลุ่ม Ex. #FFFFFF</p>"
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "5_SET_Employee",
    "description": "<p>Get course information for Employee.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>Access Token ที่ใช้ในการเรียก Service ต่าง ๆ</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ4MjUxNDE1YzcxODAyNjVjYmZiZmMwZmY2M2M5YWMwZGMzNzY3NmUzYjNjMDU4ZjA0MzRmMzY1ODQxMjAwN2IwODEzNzhkNzdkNWM0YmFiIn0.eyJhdWQiOiIzIiwianRpIjoiNDgyNTE0MTVjNzE4MDI2NWNiZmJmYzBmZjYzYzlhYzBkYzM3Njc2ZTNiM2MwNThmMDQzNGYzNjU4NDEyMDA3YjA4MTM3OGQ3N2Q1YzRiYWIiLCJpYXQiOjE1MDIxODgyOTksIm5iZiI6MTUwMjE4ODI5OSwiZXhwIjoxNTAyMTk5MDk5LCJzdWIiOiI1Iiwic2NvcGVzIjpbXX0.jB_cuSSSb1lKVKaIwe3sSsFdsZ2h0xN7pGnXAIePn97Q7rpOC9G2IW_3h_-dOlo9X9I2FWn-Qrog48zMhCa720uzzgHYKvDx2GSfN7LbDL8o0J1_W01KrFpOWJ8JY7uvaZI49Sj_x-EeV8fdN1uUv4eTMQV0vXxzcBReeknyBhsxjn9RJArP1-ruSXXoGGUco820QKS13hqiOAqlXnutPCcYtLJ-ozFWQin6plxvXR6MGdUeY9ZN3sTvS9CtQy_QCLw187uF_SI6zuX8UWfmcfCJur8rCMqBLrNAM67rFk9T518qF7MOOiSr9jue9h9CnMnzK9gwoH02mMSH-dD6W80sspXr5zmXeer-HD7xBUsbU-UOrrMPG1whTsJKPuflKzfiaC6cGFsZ90I79DAH2SBV35AjUDuJEd75Y1ovlHBoS4HxR2PvbfGSH5ZXWdCraF1uf1x68I1yF4-lk6p8A567PWrpaIFhPfUwcjhHNE91Hnpn6NPOWvTsMNgptzLMlNKvAkFOd0-JkJxVj5W5FycADEdQdhbCcdH4C0O5fq8I2AXFDcv1-lXo9JQr1eaJXi1gNhupnXQAKPAZ8kahSAA_EoWmneUrc6EomRtlHCNZzy8Ux4oHFEKMh8e_WoJFFO2QD62yZR69WrS4AELYG1MV3tbFWTS0Ulk8SuDIQ8M\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses/:course_id",
    "title": "E3: View Course Info",
    "name": "E3__View_Course_Info",
    "group": "5_SET_Employee",
    "description": "<p>Get course information for Employee.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "action",
            "description": "<p>หน้าเพจที่จะ Redirect หลังจาก Process เสร็จ</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 302 (Redirect - If session already Redirect to the action page)": [
          {
            "group": "Success 302 (Redirect - If session already Redirect to the action page)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "5_SET_Employee",
    "description": "<p>Download e-cerfiticate file for Employee.</p>",
    "version": "1.1.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-1-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  },
  {
    "type": "post",
    "url": "/set/SETEmployee/courses/:course_id/download/certificate",
    "title": "E4: Download Certificate",
    "name": "E4__Download_Certificate",
    "group": "5_SET_Employee",
    "description": "<p>Download e-cerfiticate file for Employee.</p>",
    "version": "1.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Int(3)",
            "optional": false,
            "field": ":course_id",
            "description": "<p>รหัสอ้างอิงหลักสูตร</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "company_code",
            "description": "<p>รหัสอ้างอิง Company</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "user_profile",
            "description": "<p>ข้อมูลผู้ใช้งาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Long",
            "optional": false,
            "field": "user_profile.ref_id",
            "description": "<p>รหัสอ้างอิงผู้ใช้ (User reference)</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.first_name",
            "description": "<p>ชื่อ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.last_name",
            "description": "<p>นามสกุล</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.email",
            "description": "<p>อีเมลล์</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.position_id",
            "description": "<p>ตำแหน่งงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.department",
            "description": "<p>ฝ่ายงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(13)",
            "optional": false,
            "field": "user_profile.occupation_id",
            "description": "<p>รหัสพนักงาน</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.table_number",
            "description": "<p>เบอร์โต๊ะ</p>"
          },
          {
            "group": "Parameter",
            "type": "Varchar(128)",
            "optional": false,
            "field": "user_profile.chief_name",
            "description": "<p>ชื่อ นามสกุล หัวหน้า</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200 (Download e-Certificate PDF File)": [
          {
            "group": "Success 200 (Download e-Certificate PDF File)",
            "optional": false,
            "field": "-",
            "description": ""
          }
        ]
      }
    },
    "filename": "api-sso/build-1-0-0.php",
    "groupTitle": "5_SET_Employee",
    "error": {
      "fields": {
        "Error 4xx": [
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "isSuccess",
            "description": "<p><code>TRUE</code> = การตรวจสอบข้อมูลผ่าน<br><code>FALSE</code> = การตรวจสอบข้อมูลไม่ผ่าน</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Boolean",
            "optional": false,
            "field": "trusted",
            "description": "<p><code>TRUE</code> = ข้อมูลน่าเชื่อถือ<br><code>FALSE</code> = ข้อมูลไม่น่าเชื่อถือ</p>"
          },
          {
            "group": "Error 4xx",
            "type": "DateTime",
            "optional": false,
            "field": "expired",
            "description": "<p>วันและเวลาหมดอายุของข้อมูล</p>"
          },
          {
            "group": "Error 4xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาด</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>ข้อมูลที่ส่งมา (Plain Data)</p>"
          },
          {
            "group": "Error 4xx",
            "type": "Object",
            "optional": false,
            "field": "invalid_params",
            "description": "<p>รายละเอียดข้อผิดพลาดตามฟิล์ด</p>"
          }
        ],
        "Error 5xx": [
          {
            "group": "Error 5xx",
            "type": "String",
            "optional": false,
            "field": "message",
            "description": "<p>รายละเอียดข้อผิดพลาดของฝั่งเซิฟเวอร์</p>"
          }
        ]
      }
    }
  }
] });
