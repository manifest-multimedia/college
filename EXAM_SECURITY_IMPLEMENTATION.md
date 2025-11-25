# Exam Security Implementation - Copy Protection & Context Menu Restriction

## Overview
This document describes the implementation of security measures to prevent students from copying exam questions or accessing the browser context menu during online examinations.

## Implementation Date
November 25, 2025

## Problem Statement
Students need to be prevented from:
1. Copying exam questions and options via keyboard shortcuts (Ctrl+C, Cmd+C)
2. Copying via right-click context menu
3. Selecting and dragging text
4. Using select-all functionality (Ctrl+A, Cmd+A)
5. Cutting text (Ctrl+X, Cmd+X)

## Solution Architecture

### 1. Security Component
**File:** `resources/views/components/partials/exam-security.blade.php`

A reusable Blade component that implements comprehensive copy protection:

#### CSS-Based Protection
- Uses `user-select: none` on all exam content elements
- Targets specific classes: `.exam-protected`, `.question`, `.question-text`, `.options`, etc.
- Maintains usability for interactive elements (buttons, radio inputs)

#### JavaScript-Based Protection
Implements multiple layers of security:

1. **Context Menu Blocking**
   - Prevents right-click menu on exam content
   - Uses event bubbling to catch all context menu attempts
   
2. **Copy/Cut Event Prevention**
   - Intercepts copy and cut events
   - Clears any active text selection
   - Works across all browsers
   
3. **Keyboard Shortcut Blocking**
   - Prevents Ctrl+C / Cmd+C (copy)
   - Prevents Ctrl+X / Cmd+X (cut)
   - Prevents Ctrl+A / Cmd+A (select all)
   
4. **Text Selection Prevention**
   - Blocks `selectstart` events
   - Prevents drag-based text selection
   - Maintains radio button functionality

### 2. Protected Elements
The following exam elements are protected:

```css
.exam-protected
.question
.question-text
.options
.option-card
.option-wrapper
.form-check-label
.scrollable-questions
.questions-container
```

### 3. Integration Points

#### Active Exam Views
1. **Default Theme** - `resources/views/livewire/online-examination.blade.php`
   - Multi-question scrollable view
   - Security component included via `@include('components.partials.exam-security')`
   - Main container marked with `exam-protected` class

2. **One-by-One Theme** - `resources/views/livewire/online-examination-one.blade.php`
   - Single question navigation view
   - Same security measures applied
   - Question navigation remains functional

3. **Review Mode** - `resources/views/livewire/exam-review-mode.blade.php`
   - Read-only exam review after completion
   - Security still active to prevent sharing of questions
   - Each question card protected individually

## Technical Details

### Event Handling
- All event listeners use capturing phase (`true` parameter)
- Events are stopped from propagating to prevent bypasses
- Cleanup functions provided for proper memory management

### Browser Compatibility
- Works across Chrome, Firefox, Safari, Edge
- Handles both Ctrl (Windows/Linux) and Cmd (Mac) keys
- Fallbacks for older browsers included

### Livewire Integration
- Reinitializes after Livewire navigation
- Listens for `livewire:navigated` event
- Does not interfere with Livewire's wire:click functionality

### Performance Considerations
- Uses event delegation where possible
- Minimal DOM queries
- Efficient element matching with `closest()` method

## Testing Checklist

### Manual Testing Steps
1. **Copy Protection**
   - [ ] Try selecting text in questions
   - [ ] Try Ctrl+C / Cmd+C on selected text
   - [ ] Verify nothing is copied to clipboard
   
2. **Context Menu**
   - [ ] Right-click on questions
   - [ ] Right-click on options
   - [ ] Verify context menu doesn't appear
   
3. **Keyboard Shortcuts**
   - [ ] Try Ctrl+A / Cmd+A to select all
   - [ ] Try Ctrl+X / Cmd+X to cut
   - [ ] Verify actions are blocked
   
4. **User Experience**
   - [ ] Radio buttons still clickable
   - [ ] Navigation buttons work
   - [ ] Submit button functional
   - [ ] Timer displays correctly
   - [ ] Question overview clickable

5. **Cross-Theme Testing**
   - [ ] Test on default theme
   - [ ] Test on one-by-one theme
   - [ ] Test in review mode

6. **Browser Testing**
   - [ ] Chrome
   - [ ] Firefox
   - [ ] Safari
   - [ ] Edge

### Test Accounts
- Use any student account
- Access via exam password
- Try different exam types (mid-semester, final)

## Implementation Notes

### What's Protected
✅ Question text content  
✅ Option text content  
✅ All exam-related text  
✅ Context menu access  
✅ Keyboard copy shortcuts  

### What's NOT Protected
✅ Radio button selection (students can still answer)  
✅ Navigation buttons (prev/next/submit)  
✅ Timer functionality  
✅ Question overview navigation  
✅ Form submission  

## Security Considerations

### Limitations
⚠️ **Note:** This is a client-side security measure. Determined students could:
- Use browser developer tools to bypass restrictions
- Take screenshots of questions
- Use external camera/phone to photograph screen
- Use OCR tools on screenshots

### Best Practices
These protections are part of a layered security approach:
1. ✅ Copy/context menu restriction (this implementation)
2. ✅ Device tracking (existing in `OnlineExamination.php`)
3. ✅ Session monitoring with heartbeat (existing)
4. ✅ AI proctoring (AI Sensei)
5. ✅ Time restrictions
6. ✅ Watermarking (student name on exam)

## Files Modified

### New Files
- `resources/views/components/partials/exam-security.blade.php` (created)

### Modified Files
- `resources/views/livewire/online-examination.blade.php`
- `resources/views/livewire/online-examination-one.blade.php`
- `resources/views/livewire/exam-review-mode.blade.php`

## Future Enhancements

### Potential Improvements
1. **Screenshot Detection**: Detect PrtScn key press and log attempts
2. **Browser Tab Switching**: Monitor when students switch tabs
3. **Mouse Leaving Screen**: Track when cursor leaves exam area
4. **Paste Detection**: Log if students try to paste into exam
5. **Developer Tools Detection**: Detect if DevTools are opened

### Monitoring & Analytics
Consider adding:
- Log copy/context menu attempts to database
- Track which students attempt to bypass restrictions
- Generate security reports for exam administrators

## Troubleshooting

### Issue: Protection Not Working
**Solution:** Clear browser cache and hard reload (Ctrl+Shift+R)

### Issue: Radio Buttons Not Clickable
**Solution:** Check that input elements are excluded from protection in CSS

### Issue: Navigation Broken
**Solution:** Verify event listeners aren't blocking button clicks

### Issue: Works in Chrome but Not Safari
**Solution:** Check console for browser-specific errors, verify event listener compatibility

## Related Documentation
- `MANUAL_TESTING_GUIDE.md` - General testing procedures
- `exam-prd.md` - Exam module requirements
- `prd.md` - System-wide requirements

## Support & Maintenance
For issues or enhancements:
1. Check browser console for JavaScript errors
2. Verify all includes are present in exam views
3. Test with different user roles (Student, Lecturer, Admin)
4. Review event listener logs in console

## Change Log

### Version 1.0 (November 25, 2025)
- Initial implementation of copy protection
- Context menu blocking added
- Keyboard shortcut prevention implemented
- Integration with all three exam themes
- Documentation created
