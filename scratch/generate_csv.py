import csv
import io

def generate_exam_csv():
    output = io.StringIO()
    writer = csv.writer(output)
    
    # Headers
    headers = [
        'exam_name',
        'section_title',
        'case_study_title',
        'visit_title',
        'visit_content',
        'question_text',
        'max_point',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'correct_option',
        'score_category_1',
        'content_area_1',
        'score_category_2',
        'content_area_2'
    ]
    writer.writerow(headers)
    
    exam_name = "Master Exam - 11 Sections"
    
    # Categories and Areas for rotation
    cat1 = "Counselor Work Behavior Areas (Domains)"
    areas1 = [
        "Professional Practice and Ethics",
        "Intake, Assessment and Diagnosis",
        "Treatment Planning Counseling Skills",
        "Counseling Skills and Interventions",
        "Core Counseling Attributes"
    ]
    
    cat2 = "CACREP Areas"
    areas2 = [
        "Professional Counseling Orientation and Ethical Practice",
        "Social and Cultural Diversity",
        "Human Growth and Development",
        "Career Development",
        "Counseling and Helping Relationships",
        "Group Counseling and Group Work",
        "Assessment and Testing",
        "Research and Program Evaluation"
    ]
    
    q_count = 0
    for s in range(1, 12):
        section_title = f"Section {s}: Core Domain {s}"
        case_study_title = f"Case Study {s} - Clinical Scenario"
        
        for v in range(1, 4):
            visit_title = f"Visit {v}"
            visit_content = f"This is the clinical information for Section {s}, Visit {v}. The patient presents with various symptoms and requires assessment and intervention."
            
            for q in range(1, 6):
                q_count += 1
                question_text = f"Question {q} for Section {s}, Visit {v}: What is the most appropriate next step in this clinical scenario?"
                
                # Rotate areas
                area1 = areas1[(s-1) % len(areas1)]
                area2 = areas2[(q_count-1) % len(areas2)]
                
                row = [
                    exam_name,
                    section_title,
                    case_study_title,
                    visit_title,
                    visit_content,
                    question_text,
                    "1", # max_point
                    f"Option A for Q{q_count}",
                    f"Option B for Q{q_count}",
                    f"Option C for Q{q_count}",
                    f"Option D for Q{q_count}",
                    "A", # correct_option
                    cat1,
                    area1,
                    cat2,
                    area2
                ]
                writer.writerow(row)
                
    return output.getvalue()

csv_content = generate_exam_csv()
with open('c:\\Users\\saksh\\Desktop\\alain_2.0\\exam_import_11_sections.csv', 'w', encoding='utf-8') as f:
    f.write(csv_content)

print("CSV generated successfully with 165 questions.")
