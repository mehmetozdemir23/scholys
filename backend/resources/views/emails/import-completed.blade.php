<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import terminé</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .error-list {
            background: white;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        .error-item {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .error-item:last-child {
            border-bottom: none;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .stat {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Import d'utilisateurs terminé</h1>
        <p>Votre import dans Scholys a été traité</p>
    </div>

    <div class="content">
        <p>Bonjour {{ $user->firstname }},</p>
        
        <p>L'import d'utilisateurs que vous avez lancé est maintenant terminé.</p>

        <div class="stats">
            <div class="stat">
                <div class="stat-number" style="color: #059669;">{{ $successCount }}</div>
                <div class="stat-label">Utilisateurs créés</div>
            </div>
            @if($errorCount > 0)
            <div class="stat">
                <div class="stat-number" style="color: #dc2626;">{{ $errorCount }}</div>
                <div class="stat-label">Erreurs</div>
            </div>
            @endif
        </div>

        @if($successCount > 0)
            <div class="success">
                <strong>✅ Succès !</strong> {{ $successCount }} utilisateur(s) ont été créés avec succès.
                Les emails de bienvenue avec leurs mots de passe ont été envoyés.
            </div>
        @endif

        @if($hasErrors)
            <div class="warning">
                <strong>⚠️ Attention !</strong> {{ $errorCount }} ligne(s) n'ont pas pu être traitées.
            </div>

            @if(count($errors) > 0)
                <div class="error-list">
                    <h4>Détail des erreurs :</h4>
                    @foreach($errors as $error)
                        <div class="error-item">
                            <strong>Ligne {{ $error['line'] }}</strong>: {{ $error['error'] }}
                            <br>
                            <small style="color: #6b7280;">
                                {{ $error['data']['firstname'] ?? '' }} {{ $error['data']['lastname'] ?? '' }} 
                                ({{ $error['data']['email'] ?? '' }})
                            </small>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <p style="margin-top: 30px;">
            Vous pouvez maintenant vous connecter à votre tableau de bord Scholys pour gérer vos utilisateurs.
        </p>
    </div>

    <div class="footer">
        <p>
            Cet email a été envoyé automatiquement par Scholys.<br>
            Si vous avez des questions, n'hésitez pas à nous contacter.
        </p>
    </div>
</body>
</html>