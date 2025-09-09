#!/bin/bash
# ==============================================================================
# test-userdata-scripts.sh - テスト用スクリプト
# ==============================================================================
# UserDataスクリプトの構文チェックと基本的な検証を行うテストスクリプト
# 実際の実行は行わず、シェルスクリプトの構文やファイル存在をチェック
# ==============================================================================

set -e

# スクリプトのベースディレクトリ
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
USERDATA_DIR="$SCRIPT_DIR/userdata"

echo "========================================================================"
echo "🧪 UserData Scripts Test Suite"
echo "========================================================================"
echo "Testing directory: $USERDATA_DIR"
echo

# テスト結果の追跡
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# テスト関数
test_script() {
    local script_name="$1"
    local script_path="$USERDATA_DIR/$script_name"
    
    echo "📋 Testing: $script_name"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    # ファイル存在チェック
    if [[ ! -f "$script_path" ]]; then
        echo "❌ FAIL: File not found - $script_path"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
    
    # 実行権限チェック
    if [[ ! -x "$script_path" ]]; then
        echo "⚠️  WARN: File is not executable - $script_path"
    fi
    
    # Shebang行チェック
    local shebang=$(head -n1 "$script_path")
    if [[ "$shebang" != "#!/bin/bash"* ]]; then
        echo "❌ FAIL: Invalid shebang - expected '#!/bin/bash', got '$shebang'"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
    
    # 構文チェック
    if bash -n "$script_path"; then
        echo "✅ PASS: Syntax check passed"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        return 0
    else
        echo "❌ FAIL: Syntax check failed"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        return 1
    fi
}

# 各スクリプトをテスト
echo "Starting individual script tests..."
echo

test_script "main.sh"
test_script "01-system-setup.sh"
test_script "02-ebs-mount.sh" 
test_script "03-php-setup.sh"
test_script "04-nginx-setup.sh"
test_script "05-cloudflare-ssl.sh"
test_script "06-composer-tools.sh"
test_script "07-monitoring.sh"

echo
echo "========================================================================"
echo "📊 Test Results Summary"
echo "========================================================================"
echo "Total Tests: $TOTAL_TESTS"
echo "Passed: $PASSED_TESTS"
echo "Failed: $FAILED_TESTS"

if [[ $FAILED_TESTS -eq 0 ]]; then
    echo "🎉 All tests passed!"
    echo
    echo "✨ Additional Checks:"
    echo "  • All scripts have proper shebang lines"
    echo "  • All scripts pass bash syntax validation"
    echo "  • Scripts are ready for deployment"
    echo
    echo "📝 Next Steps:"
    echo "  1. Test the modular CloudFormation template"
    echo "  2. Deploy to staging environment"
    echo "  3. Validate full WordPress setup"
    exit 0
else
    echo "💥 $FAILED_TESTS test(s) failed!"
    echo
    echo "🔧 Please fix the issues above before deployment."
    exit 1
fi