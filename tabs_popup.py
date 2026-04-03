import sys
import os

file_path = r'c:\Users\6315\Desktop\MobileApps\RND\esecrm\resources\views\inc\task\popup.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Instead of relying on exact lines, we'll replace the sections with tabs.
start_marker = r'{{-- LEFT COLUMN (Main Content) --}}'
end_marker = r'{{-- RIGHT COLUMN (Sidebar Details) --}}'

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx == -1 or end_idx == -1:
    print('Markers not found')
    sys.exit(1)

# Description section
desc_start = content.find('<div class="cf-section-title"><i class="bx bx-align-left"></i> Description</div>', start_idx)
desc_end = content.find('{{-- Attachments --}}', desc_start)
desc_content = content[desc_start:desc_end].replace('<div class="cf-section-title"><i class="bx bx-align-left"></i> Description</div>', '')

# Attachments section
att_start = content.find('<div class="cf-section-title mt-4"><i class="bx bx-paperclip"></i> Attachments', desc_end)
att_end = content.find('{{-- Comments --}}', att_start)
# Remove the section title for tabs
att_title_end = content.find('</div>', att_start) + 6
att_content = content[att_title_end:att_end]

# Comments section
com_start = content.find('<div class="cf-section-title mt-4"><i class="bx bx-comment-dots"></i> Comments</div>', att_end)
com_end = end_idx
com_content = content[com_start:com_end].replace('<div class="cf-section-title mt-4"><i class="bx bx-comment-dots"></i> Comments</div>', '')

tabs_html = f'''{{{{-- LEFT COLUMN (Main Content) --}}}}
                    <div class="col-lg-8 pe-lg-4 border-end">
                        <style>
                            .cf-nav-tabs {{ border-bottom: 1px solid #d1d5db; margin-bottom: 20px; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; }}
                            .cf-nav-tabs::-webkit-scrollbar {{ height: 4px; }}
                            .cf-nav-tabs::-webkit-scrollbar-thumb {{ background: #c1c5cb; border-radius: 4px; }}
                            .cf-nav-tabs .nav-item {{ margin-bottom: -1px; }}
                            .cf-nav-tabs .nav-link {{ color: #6c757d; font-weight: 600; border: none; border-bottom: 3px solid transparent; background: transparent !important; padding: 10px 16px; transition: 0.2s; white-space: nowrap; }}
                            .cf-nav-tabs .nav-link.active {{ color: #006666; border-bottom: 3px solid #006666; }}
                            .cf-nav-tabs .nav-link:hover:not(.active) {{ border-bottom: 3px solid #d1d5db; color: #495057; }}
                        </style>

                        <ul class="nav nav-tabs cf-nav-tabs" id="taskTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">
                                    <i class="bx bx-align-left"></i> Description
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="attach-tab" data-bs-toggle="tab" data-bs-target="#attach" type="button" role="tab">
                                    <i class="bx bx-paperclip"></i> Attachments (<span id="attachmentCountTab">{{{{ count($taskAttachments ?? []) }}}}</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="comment-tab" data-bs-toggle="tab" data-bs-target="#comment" type="button" role="tab">
                                    <i class="bx bx-comment-dots"></i> Comments
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="taskTabsContent">
                            {{{{-- Tab 1: Description --}}}}
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                {desc_content}
                            </div>
                            
                            {{{{-- Tab 2: Attachments --}}}}
                            <div class="tab-pane fade" id="attach" role="tabpanel">
                                <div class="pt-1">
                                    {att_content}
                                </div>
                            </div>
                            
                            {{{{-- Tab 3: Comments --}}}}
                            <div class="tab-pane fade" id="comment" role="tabpanel">
                                {com_content}
                            </div>
                        </div>
                    </div>
                    
                    '''

new_full_content = content[:start_idx] + tabs_html + content[end_idx:]

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_full_content)

print('Tabs successfully created')
