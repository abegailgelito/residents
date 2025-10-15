<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Hotlines</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .contact-widget {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
    
        .card-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            /* align-items: center;
            justify-content: center; */
            gap: 12px;
            position: relative;
        }
        
        .card-title i {
            font-size: 1.8rem;
        }
        
        
        .hotline-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* padding: 16px 0; */
            border-bottom: 1px solid #243c74;
            margin-bottom: 10px;
        }
        
        .hotline-item:last-child {
            border-bottom: none;
        }
        
        
        .agency {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .agency i {
            color: #243c74;
            width: 24px;
            text-align: center;
        }

        .agency-icon {
            width: 30px; 
            height: 30px;
            object-fit: contain;
        }

        
        .number {
            font-weight: 700;
            color: #243c74;
            font-size: 15px;
            letter-spacing: 0.5px;
            text-align: right;
        }
        
        
        @media (max-width: 992px) {

    /* Tablet view for contact widget */
    .contact-widget {
        max-width: 100%;
        padding: 20px;
    }

    .card-title {
        font-size: 1.4rem;
    }

    .card-title i {
        font-size: 1.6rem;
    }

    .agency {
        font-size: 14px;
    }

    .number {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .header-title {
        font-size: 1.2rem;
    }

    .contact-widget {
        padding: 15px;
    }

    /* Mobile view for contact widget */
    .agency {
        font-size: 13px;
    }

    .number {
        font-size: 13px;
    }

    .agency-icon {
        width: 25px;
        height: 25px;
    }
}
    </style>
</head>
<body>
    <div class="contact-widget">
            <h5 class="card-title">
                Emergency Hotlines
            </h5>
        <div class="hotline-list">
            <div class="hotline-item">
                <div class="agency">
                    <img src="images/qc.png" alt="Quezon City" class="agency-icon">
                        Quezon City
                </div>
                    <div class="number">122</div>
            </div>
            <div class="hotline-item">
                <div class="agency">
                    <img src="images/qcdrrmo.png" alt="QCDRRMO" class="agency-icon">
                        QCDRRMO
                </div>
                <div class="number">8475-5175 / 8927-5914 / 8928-4396</div>
            </div>
            <div class="hotline-item">
                <div class="agency">
                    <img src="images/bhert.png" alt="BHERT" class="agency-icon"> 
                        BHERT
                </div>
                <div class="number">0921-886-4832 / 0915-598-5156</div>
            </div>
            <div class="hotline-item">
                <div class="agency">
                    <img src="images/bpso.png" alt="BPSO" class="agency-icon"> 
                        BPSO
                </div>
                <div class="number">0921-876-3262</div>
            </div>
        </div>
</body>
</html>