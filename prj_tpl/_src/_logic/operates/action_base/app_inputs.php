<?php
class AppInputRuleLib 
{/*{{{*/
    static public function setup()
    {/*{{{*/
        InputRuleLib::setup();
        InputRuleLib::register("#logname",InputRuleLib::ref("#person"));
        InputRuleLib::register("#passwd",new LengthRule(5,20));
        InputRuleLib::register2("#age",InputRuleLib::ref("#digit"), new DigitScopeRule(4,30));
        InputRuleLib::register("#nodekey",new LengthRule(2,20));
        InputRuleLib::register("#objname",new LengthRule(3,60));
        InputRuleLib::register2("#mywinpath",InputRuleLib::ref('#winpath'),InputRuleLib::ref('#nospace'));
        InputRuleLib::register2("#realname",InputRuleLib::ref('#chinese'),new LengthRule(6,100));
        InputRuleLib::register2("#select",InputRuleLib::ref('#digit'),new DigitScopeRule(0,99));
        InputRuleLib::register("#pdtname",new LengthRule(3,20));
        InputRuleLib::register("#stgname",InputRuleLib::ref('#objname'));
        InputRuleLib::register("#custname",InputRuleLib::ref('#objname'));
        InputRuleLib::register("#payee",new LengthRule(2,20));
        InputRuleLib::register("#select",new RegexRule("/.+/","您没有选择[name]"));
    }/*}}}*/
}/*}}}*/
?>
