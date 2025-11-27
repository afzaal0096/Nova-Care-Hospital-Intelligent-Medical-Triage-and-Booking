# ml_api.py (Simple & Stable Version)
from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
import re

try:
    # 1. SIRF 4 COLUMNS READ KAREIN (Simple Data)
    data = pd.read_csv(
        'symptoms_data.csv', 
        header=None, 
        names=['symptom1', 'symptom2', 'disease', 'specialization'], 
        sep=',' 
    )
    data = data.iloc[1:] # Header hatao

    # Data safai
    data['symptom1'] = data['symptom1'].str.strip()
    data['symptom2'] = data['symptom2'].str.strip()
    
    # Rules load karo
    DATASET_RULES = data.set_index(['symptom1', 'symptom2'])[['disease', 'specialization']].to_dict('index')
    print(f"✅ ML Model Loaded. Rules: {len(DATASET_RULES)}")

except Exception as e:
    print(f"❌ FATAL ERROR: {e}")
    exit()

app = Flask(__name__)

def predict_specialization(symptoms_text):
    symptoms_text = symptoms_text.lower()
    predictions = {} 
    
    for (s1, s2), result_data in DATASET_RULES.items():
        matched_symptoms = []
        s1_lower = str(s1).lower()
        s2_lower = str(s2).lower()

        # Simple Keyword Matching
        if s1_lower in symptoms_text: matched_symptoms.append(s1)
        if s2_lower in symptoms_text: matched_symptoms.append(s2)
            
        if matched_symptoms:
            spec = result_data['specialization']
            disease = result_data['disease']
            score = len(matched_symptoms)

            # Agar score zyada hai toh update karo
            if spec not in predictions or score > predictions[spec]['score']:
                predictions[spec] = {
                    'disease': disease,
                    'specialization': spec,
                    'matched_symptoms': ', '.join(matched_symptoms),
                    'score': score
                }
                
    # Results ko list mein badlo aur sort karo
    final_results = list(predictions.values())
    final_results.sort(key=lambda x: x['score'], reverse=True)
    
    # Agar koi result nahi mila
    if not final_results:
        return [{
            'disease': 'General Check-up',
            'specialization': 'General Physician',
            'matched_symptoms': 'None'
        }]
        
    return final_results

@app.route('/predict_disease', methods=['POST'])
def predict_disease():
    data = request.json
    symptoms = data.get('symptoms', '')
    prediction = predict_specialization(symptoms)
    return jsonify({'predictions': prediction})

if __name__ == '__main__':
    # Note: IP wohi rakhein jo aapke terminal mein chalta hai (e.g. 192.168... ya 0.0.0.0)
    print("Server Starting...")
    app.run(host='0.0.0.0', port=5000)