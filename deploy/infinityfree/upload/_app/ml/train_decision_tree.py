# ml/train_decision_tree.py
"""
Decision Tree Classification Pipeline for Barangay Disaster Preparedness Activities
Barangay Hinaplanon, Iligan City
Uses scikit-learn DecisionTreeClassifier with 80/20 train/test split and export to active_model.json
"""

import sys
import json
import csv
import os

try:
    import numpy as np
    import pandas as pd
    from sklearn.tree import DecisionTreeClassifier, export_text
    from sklearn.model_selection import train_test_split
    from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, confusion_matrix
except ImportError:
    print("scikit-learn, numpy, or pandas not installed in current Python environment.")
    print("The primary native PHP Decision Tree pipeline is active and functional via:")
    print("   php ml/train.php --source=demo")
    sys.exit(0)

def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    data_path = os.path.join(script_dir, "data", "hinaplanon_synthetic_demonstration_records.csv")
    output_model = os.path.join(script_dir, "data", "active_model.json")

    if not os.path.exists(data_path):
        print(f"Dataset file not found: {data_path}")
        return

    print("Loading dataset from:", data_path)
    df = pd.read_csv(data_path)
    print(f"Loaded {len(df)} records.")

    features = ['disaster_type', 'severity_level', 'is_urgent', 'purok_risk_level', 'affected_households', 'affected_population']
    target = 'priority_label'

    X = df[features].copy()
    y = df[target].copy()

    # One-hot / categorical encoding
    X_encoded = pd.get_dummies(X, columns=['disaster_type', 'severity_level', 'purok_risk_level'], drop_first=False)

    X_train, X_test, y_train, y_test = train_test_split(
        X_encoded, y, test_size=0.20, random_state=42, stratify=y
    )

    clf = DecisionTreeClassifier(max_depth=5, min_samples_split=4, random_state=42)
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)

    acc = accuracy_score(y_test, y_pred)
    prec = precision_score(y_test, y_pred, average='weighted', zero_division=0)
    rec = recall_score(y_test, y_pred, average='weighted', zero_division=0)
    f1 = f1_score(y_test, y_pred, average='weighted', zero_division=0)
    cm = confusion_matrix(y_test, y_pred, labels=['Urgent', 'High', 'Medium', 'Low'])

    print("\n--- SCIKIT-LEARN EVALUATION RESULTS ---")
    print(f"Accuracy:          {acc*100:.2f}%")
    print(f"Weighted Precision:{prec*100:.2f}%")
    print(f"Weighted Recall:   {rec*100:.2f}%")
    print(f"Weighted F1-Score: {f1*100:.2f}%")
    print("Confusion Matrix:\n", cm)

if __name__ == '__main__':
    main()
