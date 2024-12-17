## Planning du Projet

```mermaid
gantt
    title Planning MaBoussole CRM v2
    dateFormat  YYYY-MM-DD
    section Sprint 1
    Configuration Système     :done, 2024-12-01, 2024-12-14
    section Sprint 2
    Auth & Permissions       :active, 2024-12-15, 2024-12-28
    section Sprint 3
    Gestion Prospects        :2024-12-29, 2024-01-11
    section Sprint 4
    Documents Prospects      :2024-01-12, 2024-01-25
    section Sprint 5
    Conversion Clients       :2024-01-26, 2024-02-08
```

## Métriques et KPIs

### Couverture des Tests
<div style="width: 500px; height: 300px;">
    <canvas id="testCoverageChart"></canvas>
</div>

### Progression des Sprints
<div style="width: 600px; height: 400px;">
    <canvas id="sprintProgressChart"></canvas>
</div>

## Tableau de Bord des KPIs

```mermaid
graph LR
    A[Tests Unitaires] -->|96%| B[Couverture Globale]
    C[Tests Intégration] -->|92%| B
    D[Tests E2E] -->|89%| B
    B -->|92%| E[Qualité Code]
    
    F[Sprint 1] -->|100%| G[Progression]
    H[Sprint 2] -->|60%| G
    I[Sprint 3] -->|0%| G
    
    J[Bugs] -->|0 Critiques| K[Santé Projet]
    L[Performance] -->|245ms| K
    M[Sécurité] -->|100%| K
```
