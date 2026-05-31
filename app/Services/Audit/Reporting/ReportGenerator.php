<?php

namespace App\Services\Audit\Reporting;

use App\DataModels\Audit\AuditReport;
use App\DataModels\Audit\AuditResult;
use Illuminate\Support\Facades\Log;

/**
 * ReportGenerator Service
 * 
 * Generates formatted audit reports in JSON, HTML, and Markdown formats.
 * Implements recommendation prioritization by severity, impact, and effort.
 * 
 * @package App\Services\Audit\Reporting
 */
class ReportGenerator
{
    /**
     * Generate a full report in the specified format
     * 
     * @param AuditReport $report The audit report to format
     * @param string $format Output format: 'json', 'html', or 'markdown'
     * @return string Formatted report content
     */
    public function generateFullReport(AuditReport $report, string $format = 'html'): string
    {
        return match ($format) {
            'json' => $this->generateJsonReport($report),
            'html' => $this->generateHtmlReport($report),
            'markdown', 'md' => $this->generateMarkdownReport($report),
            default => $this->generateHtmlReport($report),
        };
    }

    /**
     * Generate JSON report
     * 
     * @param AuditReport $report
     * @return string JSON-formatted report
     */
    public function generateJsonReport(AuditReport $report): string
    {
        $data = $report->toArray();
        $data['prioritized_recommendations'] = $this->prioritizeRecommendations($report);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Generate HTML report with styling
     * 
     * @param AuditReport $report
     * @return string HTML-formatted report
     */
    public function generateHtmlReport(AuditReport $report): string
    {
        $summary = $report->getSummary();
        $results = $report->getResultsSortedBySeverity();
        $recommendations = $this->prioritizeRecommendations($report);
        $timestamp = $report->getTimestamp()->format('Y-m-d H:i:s');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Report - {$report->getId()}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a237e 0%, #283593 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 24px; }
        .header h1 { font-size: 24px; margin-bottom: 8px; }
        .header .meta { font-size: 14px; opacity: 0.85; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .summary-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; }
        .summary-card .count { font-size: 32px; font-weight: 700; }
        .summary-card .label { font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .critical .count { color: #d32f2f; }
        .high .count { color: #f57c00; }
        .medium .count { color: #fbc02d; }
        .low .count { color: #388e3c; }
        .info .count { color: #1976d2; }
        .section { background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden; }
        .section-header { padding: 16px 20px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; font-size: 18px; font-weight: 600; }
        .finding { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; }
        .finding:last-child { border-bottom: none; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-critical { background: #ffebee; color: #c62828; }
        .badge-high { background: #fff3e0; color: #e65100; }
        .badge-medium { background: #fffde7; color: #f57f17; }
        .badge-low { background: #e8f5e9; color: #2e7d32; }
        .badge-info { background: #e3f2fd; color: #1565c0; }
        .finding-title { font-weight: 600; margin: 8px 0 4px; }
        .finding-meta { font-size: 13px; color: #888; }
        .recommendation { padding: 12px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: flex-start; gap: 12px; }
        .recommendation:last-child { border-bottom: none; }
        .rec-number { background: #1a237e; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛡️ Audit Report</h1>
        <div class="meta">ID: {$report->getId()} | Generated: {$timestamp}</div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="count">{$summary['total_findings']}</div>
            <div class="label">Total Findings</div>
        </div>
        <div class="summary-card critical">
            <div class="count">{$summary['by_severity']['critical']}</div>
            <div class="label">Critical</div>
        </div>
        <div class="summary-card high">
            <div class="count">{$summary['by_severity']['high']}</div>
            <div class="label">High</div>
        </div>
        <div class="summary-card medium">
            <div class="count">{$summary['by_severity']['medium']}</div>
            <div class="label">Medium</div>
        </div>
        <div class="summary-card low">
            <div class="count">{$summary['by_severity']['low']}</div>
            <div class="label">Low</div>
        </div>
        <div class="summary-card info">
            <div class="count">{$summary['by_severity']['info']}</div>
            <div class="label">Info</div>
        </div>
    </div>

HTML;

        // Findings section
        $html .= '    <div class="section">' . "\n";
        $html .= '        <div class="section-header">📋 Findings</div>' . "\n";

        if (empty($results)) {
            $html .= '        <div class="finding"><em>No findings detected.</em></div>' . "\n";
        } else {
            foreach ($results as $result) {
                $severityClass = 'badge-' . $result->getSeverity();
                $html .= '        <div class="finding">' . "\n";
                $html .= '            <span class="badge ' . $severityClass . '">' . strtoupper($result->getSeverity()) . '</span>' . "\n";
                $html .= '            <span class="badge">' . htmlspecialchars($result->getType()) . '</span>' . "\n";
                $html .= '            <div class="finding-title">' . htmlspecialchars($result->getMessage()) . '</div>' . "\n";
                $html .= '            <div class="finding-meta">Category: ' . htmlspecialchars($result->getCategory()) . ' | Detected: ' . $result->getTimestamp()->format('Y-m-d H:i:s') . '</div>' . "\n";

                $details = $result->getDetails();
                if (!empty($details)) {
                    $html .= '            <details style="margin-top:8px;"><summary style="cursor:pointer;color:#1976d2;font-size:13px;">View Details</summary>' . "\n";
                    $html .= '            <pre style="background:#f5f5f5;padding:12px;border-radius:6px;font-size:12px;margin-top:8px;overflow-x:auto;">' . htmlspecialchars(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>' . "\n";
                    $html .= '            </details>' . "\n";
                }

                $html .= '        </div>' . "\n";
            }
        }

        $html .= '    </div>' . "\n\n";

        // Recommendations section
        $html .= '    <div class="section">' . "\n";
        $html .= '        <div class="section-header">💡 Prioritized Recommendations</div>' . "\n";

        if (empty($recommendations)) {
            $html .= '        <div class="recommendation"><em>No recommendations at this time.</em></div>' . "\n";
        } else {
            foreach ($recommendations as $index => $rec) {
                $num = $index + 1;
                $html .= '        <div class="recommendation">' . "\n";
                $html .= '            <div class="rec-number">' . $num . '</div>' . "\n";
                $html .= '            <div>' . htmlspecialchars($rec) . '</div>' . "\n";
                $html .= '        </div>' . "\n";
            }
        }

        $html .= '    </div>' . "\n\n";

        // Footer
        $html .= <<<HTML
    <div class="footer">
        Generated by Audit System &mdash; {$timestamp}
    </div>
</div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Generate Markdown report
     * 
     * @param AuditReport $report
     * @return string Markdown-formatted report
     */
    public function generateMarkdownReport(AuditReport $report): string
    {
        $summary = $report->getSummary();
        $results = $report->getResultsSortedBySeverity();
        $recommendations = $this->prioritizeRecommendations($report);
        $timestamp = $report->getTimestamp()->format('Y-m-d H:i:s');

        $md = "# 🛡️ Audit Report\n\n";
        $md .= "**ID**: `{$report->getId()}`  \n";
        $md .= "**Generated**: {$timestamp}  \n\n";
        $md .= "---\n\n";

        // Summary
        $md .= "## Summary\n\n";
        $md .= "| Metric | Count |\n";
        $md .= "|--------|-------|\n";
        $md .= "| Total Findings | {$summary['total_findings']} |\n";
        $md .= "| 🔴 Critical | {$summary['by_severity']['critical']} |\n";
        $md .= "| 🟠 High | {$summary['by_severity']['high']} |\n";
        $md .= "| 🟡 Medium | {$summary['by_severity']['medium']} |\n";
        $md .= "| 🟢 Low | {$summary['by_severity']['low']} |\n";
        $md .= "| 🔵 Info | {$summary['by_severity']['info']} |\n\n";

        // By Type
        if (!empty($summary['by_type'])) {
            $md .= "### By Type\n\n";
            $md .= "| Type | Count |\n";
            $md .= "|------|-------|\n";
            foreach ($summary['by_type'] as $type => $count) {
                $md .= "| {$type} | {$count} |\n";
            }
            $md .= "\n";
        }

        // Findings
        $md .= "---\n\n";
        $md .= "## Findings\n\n";

        if (empty($results)) {
            $md .= "_No findings detected._\n\n";
        } else {
            foreach ($results as $result) {
                $severityIcon = match ($result->getSeverity()) {
                    'critical' => '🔴',
                    'high' => '🟠',
                    'medium' => '🟡',
                    'low' => '🟢',
                    'info' => '🔵',
                    default => '⚪',
                };

                $md .= "### {$severityIcon} [{$result->getSeverity()}] {$result->getMessage()}\n\n";
                $md .= "- **Type**: {$result->getType()}\n";
                $md .= "- **Category**: {$result->getCategory()}\n";
                $md .= "- **Detected**: {$result->getTimestamp()->format('Y-m-d H:i:s')}\n";

                $details = $result->getDetails();
                if (!empty($details)) {
                    $md .= "\n<details>\n<summary>Details</summary>\n\n";
                    $md .= "```json\n";
                    $md .= json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                    $md .= "```\n\n";
                    $md .= "</details>\n";
                }
                $md .= "\n";
            }
        }

        // Recommendations
        $md .= "---\n\n";
        $md .= "## 💡 Prioritized Recommendations\n\n";

        if (empty($recommendations)) {
            $md .= "_No recommendations at this time._\n\n";
        } else {
            foreach ($recommendations as $index => $rec) {
                $num = $index + 1;
                $md .= "{$num}. {$rec}\n";
            }
            $md .= "\n";
        }

        $md .= "---\n\n";
        $md .= "_Generated by Audit System — {$timestamp}_\n";

        return $md;
    }

    /**
     * Prioritize recommendations by severity, then estimated impact, then effort
     * 
     * Critical findings first, then high, then medium, etc.
     * Within each severity, higher impact items come first.
     * 
     * @param AuditReport $report
     * @return array<int, string> Prioritized recommendation strings
     */
    public function prioritizeRecommendations(AuditReport $report): array
    {
        $recommendations = [];

        // Group findings by severity
        $criticalResults = $report->getResultsBySeverity('critical');
        $highResults = $report->getResultsBySeverity('high');
        $mediumResults = $report->getResultsBySeverity('medium');
        $lowResults = $report->getResultsBySeverity('low');

        // Critical: immediate action required
        if (count($criticalResults) > 0) {
            $recommendations[] = sprintf(
                '🚨 URGENT: %d critical issue(s) require immediate attention',
                count($criticalResults)
            );

            foreach ($criticalResults as $result) {
                $recommendations[] = sprintf(
                    '[CRITICAL] %s (Category: %s)',
                    $result->getMessage(),
                    $result->getCategory()
                );
            }
        }

        // High: resolve within 1 week
        if (count($highResults) > 0) {
            $recommendations[] = sprintf(
                '⚠️ HIGH PRIORITY: %d high-severity issue(s) should be resolved within 1 week',
                count($highResults)
            );

            foreach ($highResults as $result) {
                $recommendations[] = sprintf(
                    '[HIGH] %s (Category: %s)',
                    $result->getMessage(),
                    $result->getCategory()
                );
            }
        }

        // Medium: resolve within 1 month
        if (count($mediumResults) > 0) {
            $recommendations[] = sprintf(
                '📋 MEDIUM: %d medium-severity issue(s) should be planned for resolution within 1 month',
                count($mediumResults)
            );
        }

        // Low: resolve when convenient
        if (count($lowResults) > 0) {
            $recommendations[] = sprintf(
                'ℹ️ LOW: %d low-severity improvement(s) for best practice compliance',
                count($lowResults)
            );
        }

        // Append existing report recommendations
        foreach ($report->getRecommendations() as $rec) {
            if (!in_array($rec, $recommendations, true)) {
                $recommendations[] = $rec;
            }
        }

        return $recommendations;
    }
}
