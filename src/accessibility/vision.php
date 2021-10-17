<?php
namespace accessibility;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class vision {
    public $uFunc;
    private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->uFunc->incJs("accessibility/js/vision.min.js");
    }
}
new vision($this);

ob_start();?>

<div class="wrapper">
    <div class="box-shadow">
        <div id="content" class="cols row">

            <div class="col-md-8 col-lg-8 col-sm-6 col-xs-12">
                <div class="crm" data-mistake="content">


                    <article><section class="article">

                            <h1>Версия для слабовидящих</h1>
                            <div class="textblock">


                                <div class="browsers">
                                    <div>
                                        <h2>Использование сайта с помощью клавиатуры</h2>
                                        <p>Выберите ваш браузер:</p>
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#ie" aria-controls="ie" role="tab" data-toggle="tab">Internet Explorer</a></li>
                                            <li role="presentation"><a href="#mf" aria-controls="mf" role="tab" data-toggle="tab">Mozilla Firefox</a></li>
                                            <li role="presentation"><a href="#gc" aria-controls="gc" role="tab" data-toggle="tab">Google Chrome</a></li>
                                            <li role="presentation"><a href="#o" aria-controls="o" role="tab" data-toggle="tab">Opera</a></li>
                                            <li role="presentation"><a href="#s" aria-controls="s" role="tab" data-toggle="tab">Safari</a></li>
                                        </ul>

                                    </div>
                                    <div class="tab-content">
                                        <div class="tab-pane active" role="tabpanel" id="ie">
                                            <table>
                                                <tr><th class="l">Действие Internet Explorer</th><th class="r">Нажать</th></tr>
                                                <tr><td>Действие</td><td><span>Enter</span></td></tr>
                                                <tr><td>Переход вперед по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Tab</span></td></tr>
                                                <tr><td>Переход назад по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Shift</span> + <span>Tab</span></td></tr>
                                                <tr><td>Переход на домашнюю страницу</td><td><span>Alt</span> + <span>Home</span></td></tr>
                                                <tr><td>Переход на следующую страницу</td><td><span>Alt</span> + <span>→</span></td></tr>
                                                <tr><td>Переход на предыдущую страницу</td><td><span>Alt</span> + <span>←</span><br>или<br><span>Backspace</span></td></tr>
                                                <tr><td>Прокрутка к началу документа</td><td><span>↑</span></td></tr>
                                                <tr><td>Прокрутка к концу документа</td><td><span>↓</span></td></tr>
                                                <tr><td>Прокрутка к началу документа большими шагами</td><td><span>Page Up</span><br>или<br><span>Shift</span> + <span>Space</span></td></tr>
                                                <tr><td>Прокрутка к концу документа большими шагами</td><td><span>Page Down</span><br>или<br><span>Space</span></td></tr>
                                                <tr><td>Переход в начало документа</td><td><span>Home</span></td></tr>
                                                <tr><td>Переход в конец документа</td><td><span>End</span></td></tr>
                                                <tr><td>Поиск на текущей странице</td><td><span>Ctrl</span> + <span>F</span></td></tr>
                                                <tr><td>Вызов контекстного меню ссылки</td><td><span>Shift</span> + <span>F10</span></td></tr>
                                                <tr><td>Обновить текущую страницу</td><td><span>Ctrl</span> + <span>R</span><br>или<br><span>F5</span></td></tr>
                                                <tr><td>Прекратить загрузку страницы</td><td><span>Esc</span></td></tr>
                                                <tr><td>Сохранить текущую страницы</td><td><span>Ctrl</span> + <span>S</span></td></tr>
                                                <tr><td>Напечатать текущую страницу</td><td><span>Ctrl</span> + <span>P</span></td></tr>
                                                <tr><td>Закрыть текущую страницу</td><td><span>Ctrl</span> + <span>W</span></td></tr>
                                                <tr><td>Вывод справки</td><td><span>F1</span></td></tr>
                                                <tr><td>Переключение между полноэкранным и обычным режимами окна обозревателя</td><td><span>F11</span></td></tr>
                                                <tr><td>Увеличить</td><td><span>Ctrl</span> + <span>+</span></td></tr>
                                                <tr><td>Уменьшить</td><td><span>Ctrl</span> + <span>-</span></td></tr>
                                                <tr><td>Вернуться к 100%</td><td><span>Ctrl</span> + <span>0</span></td></tr>
                                            </table>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="mf">
                                            <table>
                                                <tr><th class="l">Действие Mozilla Firefox</th><th class="r">Нажать</th></tr>
                                                <tr><td>Действие</td><td><span>Enter</span></td></tr>
                                                <tr><td>Переход вперед по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Tab</span></td></tr>
                                                <tr><td>Переход назад по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Shift</span> + <span>Tab</span></td></tr>
                                                <tr><td>Переход на домашнюю страницу</td><td><span>Alt</span> + <span>Home</span></td></tr>
                                                <tr><td>Переход на следующую страницу</td><td><span>Alt</span> + <span>→</span><br>или<br><span>Shift</span> + <span>Backspace</span></td></tr>
                                                <tr><td>Переход на предыдущую страницу</td><td><span>Alt</span> + <span>←</span><br>или<br><span>Backspace</span></td></tr>
                                                <tr><td>Прокрутка к началу документа</td><td><span>↑</span></td></tr>
                                                <tr><td>Прокрутка к концу документа</td><td><span>↓</span></td></tr>
                                                <tr><td>Прокрутка к началу документа большими шагами</td><td><span>Page Up</span><br>или<br><span>Shift</span> + <span>Space</span></td></tr>
                                                <tr><td>Прокрутка к концу документа большими шагами</td><td><span>Page Down</span><br>или<br><span>Space</span></td></tr>
                                                <tr><td>Переход в начало документа</td><td><span>Home</span></td></tr>
                                                <tr><td>Переход в конец документа</td><td><span>End</span></td></tr>
                                                <tr><td>Найти на текущей странице</td><td><span>Ctrl</span> + <span>F</span></td></tr>
                                                <tr><td>Найти далее</td><td><span>F3</span></td></tr>
                                                <tr><td>Вернуться к предыдущему результату</td><td><span>Shift</span> + <span>F3</span></td></tr>
                                                <tr><td>Вызов контекстного меню ссылки</td><td><span>Shift</span> + <span>F10</span></td></tr>
                                                <tr><td>Обновить текущую страницу</td><td><span>Ctrl</span> + <span>R</span><br>или<br><span>F5</span></td></tr>
                                                <tr><td>Обновить текущую страницу (не используя кэш)</td><td><span>Ctrl</span> + <span>Shift</span> + <span>R</span><br>или<br><span>Ctrl</span> + <span>F5</span></td></tr>
                                                <tr><td>Прекратить загрузку страницы</td><td><span>Esc</span></td></tr>
                                                <tr><td>Сохранить текущую страницы</td><td><span>Ctrl</span> + <span>S</span></td></tr>
                                                <tr><td>Напечатать текущую страницу</td><td><span>Ctrl</span> + <span>P</span></td></tr>
                                                <tr><td>Закрыть текущую страницу</td><td><span>Ctrl</span> + <span>W</span></td></tr>
                                                <tr><td>Вывод справки</td><td><span>F1</span></td></tr>
                                                <tr><td>Переключение между полноэкранным и обычным режимами окна обозревателя</td><td><span>F11</span></td></tr>
                                                <tr><td>Режим активного курсора</td><td><span>F7</span></td></tr>
                                                <tr><td>Увеличить</td><td><span>Ctrl</span> + <span>+</span></td></tr>
                                                <tr><td>Уменьшить</td><td><span>Ctrl</span> + <span>-</span></td></tr>
                                                <tr><td>Вернуться к 100%</td><td><span>Ctrl</span> + <span>0</span></td></tr>
                                            </table>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="gc">
                                            <table>
                                                <tr><th class="l">Действие Google Chrome</th><th class="r">Нажать</th></tr>
                                                <tr><td>Действие</td><td><span>Enter</span></td></tr>
                                                <tr><td>Переход вперед по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Tab</span></td></tr>
                                                <tr><td>Переход назад по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Shift</span> + <span>Tab</span></td></tr>
                                                <tr><td>Переход на домашнюю страницу</td><td><span>Alt</span> + <span>Home</span></td></tr>
                                                <tr><td>Переход на следующую страницу</td><td><span>Alt</span> + <span>→</span><br>или<br><span>Shift</span> + <span>Backspace</span></td></tr>
                                                <tr><td>Переход на предыдущую страницу</td><td><span>Alt</span> + <span>←</span><br>или<br><span>Backspace</span></td></tr>
                                                <tr><td>Прокрутка к началу документа</td><td><span>↑</span></td></tr>
                                                <tr><td>Прокрутка к концу документа</td><td><span>↓</span></td></tr>
                                                <tr><td>Прокрутка к началу документа большими шагами</td><td><span>Page Up</span><br>или<br><span>Shift</span> + <span>Space</span></td></tr>
                                                <tr><td>Прокрутка к концу документа большими шагами</td><td><span>Page Down</span><br>или<br><span>Space</span></td></tr>
                                                <tr><td>Переход в начало документа</td><td><span>Home</span></td></tr>
                                                <tr><td>Переход в конец документа</td><td><span>End</span></td></tr>
                                                <tr><td>Найти на текущей странице</td><td><span>Ctrl</span> + <span>F</span></td></tr>
                                                <tr><td>Найти далее</td><td><span>F3</span></td></tr>
                                                <tr><td>Вернуться к предыдущему результату</td><td><span>Shift</span> + <span>F3</span></td></tr>
                                                <tr><td>Обновить текущую страницу</td><td><span>Ctrl</span> + <span>R</span><br>или<br><span>F5</span></td></tr>
                                                <tr><td>Обновить текущую страницу (не используя кэш)</td><td><span>Shift</span> + <span>F5</span><br>или<br><span>Ctrl</span> + <span>F5</span></td></tr>
                                                <tr><td>Прекратить загрузку страницы</td><td><span>Esc</span></td></tr>
                                                <tr><td>Сохранить текущую страницы</td><td><span>Ctrl</span> + <span>S</span></td></tr>
                                                <tr><td>Напечатать текущую страницу</td><td><span>Ctrl</span> + <span>P</span></td></tr>
                                                <tr><td>Закрыть текущую страницу</td><td><span>Ctrl</span> + <span>W</span></td></tr>
                                                <tr><td>Вывод справки</td><td><span>F1</span></td></tr>
                                                <tr><td>Переключение между полноэкранным и обычным режимами окна обозревателя</td><td><span>F11</span></td></tr>
                                                <tr><td>Увеличить</td><td><span>Ctrl</span> + <span>+</span></td></tr>
                                                <tr><td>Уменьшить</td><td><span>Ctrl</span> + <span>-</span></td></tr>
                                                <tr><td>Вернуться к 100%</td><td><span>Ctrl</span> + <span>0</span></td></tr>
                                            </table>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="o">
                                            <table>
                                                <tr><th class="l">Действие Opera</th><th class="r">Нажать</th></tr>
                                                <tr><td>Действие</td><td><span>Enter</span></td></tr>
                                                <tr><td>Переход вперед по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Tab</span></td></tr>
                                                <tr><td>Переход назад по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Shift</span> + <span>Tab</span></td></tr>
                                                <tr><td>Переход на домашнюю страницу</td><td><span>Alt</span> + <span>Home</span></td></tr>
                                                <tr><td>Переход на следующую страницу</td><td><span>Alt</span> + <span>→</span></td></tr>
                                                <tr><td>Переход на предыдущую страницу</td><td><span>Alt</span> + <span>←</span><br>или<br><span>Backspace</span></td></tr>
                                                <tr><td>Прокрутка к началу документа</td><td><span>↑</span></td></tr>
                                                <tr><td>Прокрутка к концу документа</td><td><span>↓</span></td></tr>
                                                <tr><td>Прокрутка к началу документа большими шагами</td><td><span>Page Up</span><br>или<br><span>Shift</span> + <span>Space</span></td></tr>
                                                <tr><td>Прокрутка к концу документа большими шагами</td><td><span>Page Down</span><br>или<br><span>Space</span></td></tr>
                                                <tr><td>Переход в начало документа</td><td><span>Home</span></td></tr>
                                                <tr><td>Переход в конец документа</td><td><span>End</span></td></tr>
                                                <tr><td>Найти на текущей странице</td><td><span>Ctrl</span> + <span>F</span></td></tr>
                                                <tr><td>Найти далее</td><td><span>F3</span></td></tr>
                                                <tr><td>Вернуться к предыдущему результату</td><td><span>Shift</span> + <span>F3</span></td></tr>
                                                <tr><td>Обновить текущую страницу</td><td><span>Ctrl</span> + <span>R</span><br>или<br><span>F5</span></td></tr>
                                                <tr><td>Прекратить загрузку страницы</td><td><span>Esc</span></td></tr>
                                                <tr><td>Сохранить текущую страницы</td><td><span>Ctrl</span> + <span>S</span></td></tr>
                                                <tr><td>Напечатать текущую страницу</td><td><span>Ctrl</span> + <span>P</span></td></tr>
                                                <tr><td>Закрыть текущую страницу</td><td><span>Ctrl</span> + <span>W</span></td></tr>
                                                <tr><td>Вывод справки</td><td><span>F1</span></td></tr>
                                                <tr><td>Переключение между полноэкранным и обычным режимами окна обозревателя</td><td><span>F11</span></td></tr>
                                                <tr><td>Увеличить</td><td><span>Ctrl</span> + <span>+</span></td></tr>
                                                <tr><td>Уменьшить</td><td><span>Ctrl</span> + <span>-</span></td></tr>
                                                <tr><td>Вернуться к 100%</td><td><span>Ctrl</span> + <span>0</span></td></tr>
                                            </table>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="s">
                                            <table>
                                                <tr><th class="l">Действие Safari</th><th class="r">Нажать</th></tr>
                                                <tr><td>Действие</td><td><span>Enter</span></td></tr>
                                                <tr><td>Переход вперед по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Tab</span></td></tr>
                                                <tr><td>Переход назад по последовательности элементов на веб-странице, в адресной строке и на панели ссылок</td><td><span>Shift</span> + <span>Tab</span></td></tr>
                                                <tr><td>Прокрутка к началу документа</td><td><span>↑</span></td></tr>
                                                <tr><td>Прокрутка к концу документа</td><td><span>↓</span></td></tr>
                                                <tr><td>Прокрутка к началу документа большими шагами</td><td><span>Page Up</span><br>или<br><span>Shift</span> + <span>Space</span></td></tr>
                                                <tr><td>Прокрутка к концу документа большими шагами</td><td><span>Page Down</span><br>или<br><span>Space</span></td></tr>
                                                <tr><td>Переход в начало документа</td><td><span>Home</span></td></tr>
                                                <tr><td>Переход в конец документа</td><td><span>End</span></td></tr>
                                                <tr><td>Найти на текущей странице</td><td><span>Cmd</span> + <span>F</span></td></tr>
                                                <tr><td>Переход на следующую страницу</td><td><span>Cmd</span> + <span>[</span></td></tr>
                                                <tr><td>Переход на предыдущую страницу</td><td><span>Cmd</span> + <span>]</span></td></tr>
                                                <tr><td>Обновить текущую страницу</td><td><span>Cmd</span> + <span>F</span><br>или<br><span>F5</span></td></tr>
                                                <tr><td>Прекратить загрузку страницы</td><td><span>Esc</span></td></tr>
                                                <tr><td>Сохранить текущую страницы</td><td><span>Cmd</span> + <span>S</span></td></tr>
                                                <tr><td>Напечатать текущую страницу</td><td><span>Cmd</span> + <span>P</span></td></tr>
                                                <tr><td>Вывод справки</td><td><span>F1</span></td></tr>
                                                <tr><td>Переключение между полноэкранным и обычным режимами окна обозревателя</td><td><span>Cmd</span> + <span>Shift</span> + <span>F</span></td></tr>
                                                <tr><td>Увеличить</td><td><span>Cmd</span> + <span>+</span></td></tr>
                                                <tr><td>Уменьшить</td><td><span>Cmd</span> + <span>-</span></td></tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section></article>


                </div>
            </div>

            <div class="col-md-4 col-lg-4 col-sm-6 col-xs-12">

                <div class="esir.ignore">

                    <div class="vision_settings contentMenu">
                        <div class="highlight">
                            <h4>Размер шрифта сайта:</h4>
                            <ul class="list-unstyled">
                                <li><input name="font_size[]" type="radio" id="normal" checked=""> <label class="s1" for="normal">Обычный</label></li>
                                <li><input name="font_size[]" type="radio" id="large"> <label for="large" class="s2">Большой</label></li>
                                <li><input name="font_size[]" type="radio" id="huge"> <label for="huge" class="s3">Очень большой</label></li>
                            </ul>
                        </div>

                        <div  class="highlight">
                            <h4>Интервал между буквами (кернинг):</h4>
                            <ul class="list-unstyled">
                                <li><input name="kern[]" type="radio" id="kern_normal" checked=""> <label for="kern_normal" class="kern_normal">Обычный</label></li>
                                <li><input name="kern[]" type="radio" id="kern_large"> <label for="kern_large" class="kern_large">Большой</label></li>
                                <li><input name="kern[]" type="radio" id="kern_huge"> <label for="kern_huge" class="kern_huge">Очень большой</label></li>
                            </ul>
                        </div>
                    </div>



                    <div class="right-banners">

                    </div>




                </div>
            </div>

        </div>



    </div>
</div>

<?
$this->page_content=ob_get_contents();
ob_end_clean();
require_once "templates/template.php";?>