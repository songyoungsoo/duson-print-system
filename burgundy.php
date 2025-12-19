from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas
from reportlab.lib.colors import Color, black, white, HexColor
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
import os

def create_flyer():
    # 1. 파일 설정
    filename = "academy_flyer_burgundy.pdf"
    c = canvas.Canvas(filename, pagesize=A4)
    width, height = A4
    
    # 2. 폰트 등록 (시스템에 있는 한글 폰트 경로를 지정해야 함)
    # 윈도우 기본 폰트인 '맑은 고딕'을 예시로 사용했습니다. 
    # 맥이나 리눅스 사용 시 경로를 변경해주세요.
    font_path = "C:/Windows/Fonts/malgun.ttf" 
    if not os.path.exists(font_path):
        # 폰트 파일이 없으면 기본 폰트로 안내 메시지 출력 (실제 사용시 폰트 경로 수정 필수)
        print("지정된 폰트 경로에 파일이 없습니다. 코드를 열어 'font_path'를 수정해주세요.")
        return

    pdfmetrics.registerFont(TTFont('MalgunGothic', font_path))
    pdfmetrics.registerFont(TTFont('MalgunGothic-Bold', "C:/Windows/Fonts/malgunbd.ttf")) # 굵은 글씨용

    # 3. 색상 정의 (버건디 & 서브 컬러)
    burgundy = HexColor('#800020')
    dark_gray = HexColor('#333333')
    light_gray = HexColor('#F2F2F2')

    # --- 디자인 요소 배치 ---

    # [배경 장식] 상단 헤더 바
    c.setFillColor(burgundy)
    c.rect(0, height - 120, width, 120, fill=1, stroke=0)

    # [헤드라인]
    c.setFillColor(white)
    c.setFont("MalgunGothic-Bold", 30)
    c.drawCentredString(width / 2, height - 70, "1:1 과외식 수업 & 소수 정예 전문TG 학원")
    
    # [소개글]
    c.setFillColor(black)
    c.setFont("MalgunGothic-Bold", 18)
    c.drawCentredString(width / 2, height - 160, "\"20년 경력의 베테랑 선생님들이 고3 입시까지 책임집니다!\"")
    
    c.setFont("MalgunGothic", 14)
    c.setFillColor(dark_gray)
    c.drawCentredString(width / 2, height - 190, "국어 · 영어 · 수학 · 과학 (네 분의 전담 선생님 상주)")

    # [강조 박스: 1주일 무료 수강] - 시선을 잡는 핵심 포인트
    # 박스 그리기
    c.setFillColor(burgundy)
    c.roundRect(50, height - 380, width - 100, 140, 10, fill=1, stroke=0)
    
    # 박스 안 텍스트
    c.setFillColor(white) # 대비를 위해 흰색 글씨 사용
    c.setFont("MalgunGothic-Bold", 24)
    c.drawCentredString(width / 2, height - 290, "📢 1주일 무료 수강 이벤트")
    
    c.setFont("MalgunGothic", 14)
    c.drawCentredString(width / 2, height - 330, "일주일 동안 먼저 수업을 받아보세요.")
    c.drawCentredString(width / 2, height - 355, "수업 방식이 아이에게 잘 맞는지 확인 후 등록하셔도 늦지 않습니다.")

    # [교육 과정 및 팁]
    c.setFillColor(dark_gray)
    c.setFont("MalgunGothic-Bold", 16)
    c.drawString(70, height - 440, "[교육 과정 및 수강료 팁]")
    
    c.setFont("MalgunGothic", 12)
    text_y = height - 470
    lines = [
        "대학 입시, 국·영·수·과 모두 놓칠 수 없습니다.",
        "2과목 수강료와 4과목 수강료의 차이가 크지 않습니다!",
        "부담 없는 비용으로 전 과목 완벽 대비를 시작하세요."
    ]
    for line in lines:
        c.drawString(70, text_y, "• " + line)
        text_y -= 25

    # [오시는 길 및 문의]
    c.setStrokeColor(burgundy)
    c.line(50, 200, width-50, 200) # 구분선

    c.setFillColor(burgundy)
    c.setFont("MalgunGothic-Bold", 16)
    c.drawString(70, 170, "[오시는 길 및 문의]")

    c.setFillColor(black)
    c.setFont("MalgunGothic", 12)
    c.drawString(70, 140, "위치: 우남아이파크 상가 2층 (E마트, 제일3차 아파트 인근)")
    
    # 연락처 강조
    c.setFont("MalgunGothic-Bold", 20)
    c.setFillColor(burgundy)
    c.drawCentredString(width / 2, 80, "상담 문의: 063-284-0703 / 010-3672-6022")

    # 약도 자리 표시 (박스)
    c.setStrokeColor(dark_gray)
    c.setDash([2, 2], 0) # 점선
    c.rect(width - 200, 130, 130, 60, fill=0, stroke=1)
    c.setFont("MalgunGothic", 10)
    c.drawCentredString(width - 135, 155, "(이곳에 약도 이미지를 넣으세요)")

    c.save()
    print(f"{filename} 생성이 완료되었습니다!")

if __name__ == "__main__":
    create_flyer()