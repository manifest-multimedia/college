{{-- 
    Exam Security Component
    Prevents text copying and context menu access during examinations
    Applies restrictions only to question content and options
--}}

<style>
    /* Prevent text selection on exam questions and options */
    .exam-protected,
    .question,
    .question-text,
    .options,
    .option-card,
    .option-wrapper,
    .form-check-label,
    .scrollable-questions,
    .questions-container {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Allow selection for input fields (so students can see their selections) */
    input[type="radio"],
    input[type="checkbox"],
    button,
    .btn {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>

<script>
    (function() {
        'use strict';

        // Function to disable context menu on exam content
        function disableContextMenu(event) {
            const examProtectedElements = [
                '.exam-protected',
                '.question',
                '.question-text',
                '.options',
                '.option-card',
                '.option-wrapper',
                '.form-check-label',
                '.scrollable-questions',
                '.questions-container'
            ];

            // Check if the clicked element or any of its parents match our protected elements
            for (let selector of examProtectedElements) {
                if (event.target.closest(selector)) {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log('Context menu disabled for exam content');
                    return false;
                }
            }
        }

        // Function to disable text selection via mouse drag
        function disableTextSelection(event) {
            const examProtectedElements = [
                '.question',
                '.question-text',
                '.options',
                '.option-card',
                '.form-check-label'
            ];

            for (let selector of examProtectedElements) {
                if (event.target.closest(selector)) {
                    event.preventDefault();
                    return false;
                }
            }
        }

        // Function to disable copy operations
        function disableCopy(event) {
            const examProtectedElements = [
                '.exam-protected',
                '.question',
                '.question-text',
                '.options',
                '.option-card',
                '.form-check-label',
                '.scrollable-questions',
                '.questions-container'
            ];

            // Check if the selection contains any exam content
            const selection = window.getSelection();
            if (selection && selection.toString().length > 0) {
                let selectionNode = selection.anchorNode;
                
                // Traverse up to find if we're in a protected element
                while (selectionNode && selectionNode !== document.body) {
                    if (selectionNode.nodeType === Node.ELEMENT_NODE) {
                        for (let selector of examProtectedElements) {
                            if (selectionNode.matches && selectionNode.matches(selector)) {
                                event.preventDefault();
                                event.stopPropagation();
                                
                                // Clear the selection
                                if (window.getSelection) {
                                    window.getSelection().removeAllRanges();
                                } else if (document.selection) {
                                    document.selection.empty();
                                }
                                
                                // Show a brief warning message (optional)
                                console.warn('Copying exam content is not allowed');
                                return false;
                            }
                        }
                    }
                    selectionNode = selectionNode.parentNode;
                }
            }
        }

        // Function to disable keyboard shortcuts for copy operations
        function disableKeyboardShortcuts(event) {
            // Detect Ctrl+C, Ctrl+X, Ctrl+A (Cmd on Mac)
            const isCopy = (event.ctrlKey || event.metaKey) && (event.key === 'c' || event.key === 'C');
            const isCut = (event.ctrlKey || event.metaKey) && (event.key === 'x' || event.key === 'X');
            const isSelectAll = (event.ctrlKey || event.metaKey) && (event.key === 'a' || event.key === 'A');
            
            if (isCopy || isCut || isSelectAll) {
                const examProtectedElements = [
                    '.question',
                    '.question-text',
                    '.options',
                    '.option-card',
                    '.form-check-label',
                    '.scrollable-questions'
                ];

                // Check if focus is within exam content
                for (let selector of examProtectedElements) {
                    if (event.target.closest(selector)) {
                        event.preventDefault();
                        event.stopPropagation();
                        console.warn('Keyboard shortcuts for copying are disabled during exams');
                        return false;
                    }
                }
            }
        }

        // Function to disable drag selection
        function disableDragSelection(event) {
            const examProtectedElements = [
                '.question',
                '.question-text',
                '.options',
                '.option-card',
                '.form-check-label'
            ];

            for (let selector of examProtectedElements) {
                if (event.target.closest(selector)) {
                    event.preventDefault();
                    return false;
                }
            }
        }

        // Initialize protection when DOM is ready
        function initializeExamSecurity() {
            console.log('Initializing exam security measures...');

            // Disable context menu
            document.addEventListener('contextmenu', disableContextMenu, true);

            // Disable copy event
            document.addEventListener('copy', disableCopy, true);

            // Disable cut event
            document.addEventListener('cut', disableCopy, true);

            // Disable keyboard shortcuts
            document.addEventListener('keydown', disableKeyboardShortcuts, true);

            // Disable text selection via mouse
            document.addEventListener('selectstart', disableTextSelection, false);

            // Disable drag selection
            document.addEventListener('dragstart', disableDragSelection, false);

            console.log('Exam security measures activated');
        }

        // Clean up function
        function cleanupExamSecurity() {
            document.removeEventListener('contextmenu', disableContextMenu, true);
            document.removeEventListener('copy', disableCopy, true);
            document.removeEventListener('cut', disableCopy, true);
            document.removeEventListener('keydown', disableKeyboardShortcuts, true);
            document.removeEventListener('selectstart', disableTextSelection, false);
            document.removeEventListener('dragstart', disableDragSelection, false);
            console.log('Exam security measures deactivated');
        }

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeExamSecurity);
        } else {
            initializeExamSecurity();
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', cleanupExamSecurity);

        // For Livewire navigation, reinitialize after navigation
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigated - reinitializing exam security');
            initializeExamSecurity();
        });

        // Make cleanup available globally if needed
        window.cleanupExamSecurity = cleanupExamSecurity;
    })();
</script>
