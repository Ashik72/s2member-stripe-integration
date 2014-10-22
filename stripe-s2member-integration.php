<?php
/*
Plugin Name: s2Member Stripe Integration
Author: Furlong Design
Author URI: http://furlongdesign.com
Version: 1.1
Description: s2Member Stripe Integration plugin connects your s2Member to Stripe and lets you charge for your memberships. Now you can add Stripe as a payment gateway to s2Member.
*/
//Media Button to Editor
add_action('media_buttons', 'stripe_editor_button', 11);
function stripe_editor_button()
{
    //our popup's title
    $title = 'Add s2Member Stripe Product';
    //append the icon
    $context .= "<a title='{$title}' href='#TB_inline?width=400&inlineId=stripe_container'
    class='thickbox button'>Add s2Member Stripe Product</a>";
    echo $context;
}

//Content in footer for popup
add_action('admin_footer', 'stripe_popup_content');
function stripe_popup_content()
{
    ?>

    <div id="stripe_container" style="display:none;">
        <h2>Insert Stripe Buttons</h2>

        <form action="" method='post'>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label for="">Choose Type</label></th>
                    <td>
                        <select id="stripe_role">
                            <?php
                            $roles = get_editable_roles();
                            foreach ($roles as $k => $role) {
                                if (strpos(strtolower($k), 's2member') !== false) {
                                    echo "<option value='" . $k . "'>" . $role['name'] . "</option>";
                                }
                            }
                            ?>
                        </select>

                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="">Custom Class</label></th>
                    <td><input type="text" id='stripe_class' value=''/></td>
                </tr>
                <tr valign="top">
                    <td><input type="button" id='insert_stripe_product' value='Insert'/></td>
                </tr>
                </tbody>
            </table>
        </form>


    </div>

<?php
}

add_action('after_wp_tiny_mce', 'stripe_after_wp_tiny_mce');
function stripe_after_wp_tiny_mce()
{
    printf('<script type="text/javascript" src="%s"></script>', plugins_url('scripts.js', __FILE__));
}

add_action('wp_head', 'stripe_js');
function stripe_js()
{
    printf('<script src="https://checkout.stripe.com/checkout.js"></script>');
}

add_action('wp_ajax_get_stripe_products', 'get_stripe_products');
function get_stripe_products()
{
    if (get_option('stripeskey')) {
        require_once('stripe/lib/Stripe.php');
        Stripe :: setApiKey(get_option('stripeskey'));
        $for_plan = Stripe_Plan2 :: all();
        $p        = json_decode($for_plan, true);
        require_once('stripe/lib/Stripe.php');
        Stripe :: setApiKey(get_option('stripeskey'));
        $for_plan = Stripe_Plan2 :: all();
        $p        = json_decode($for_plan, true);
        $roles    = get_editable_roles();
        echo "<option value=''>Select Plan</option>";
        foreach ($p['data'] as $value) {
            if (strlen(get_option('stripe_role_' . $value['id'])) > 0) {
                echo '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
            }
            $i++;
        }
    }
}

add_action('admin_menu', 'stripe_install');
function stripe_install()
{
    $menu = add_menu_page(
        's2Member Stripe',
        's2Member Stripe',
        'manage_options',
        'cwd_stripe',
        'cwd_stripe_checkout',
        ''
    );
    add_action('load-' . $menu, 'load_stripe_admin_js');
}

function load_stripe_admin_js()
{
    add_action('admin_enqueue_scripts', 'enqueue_stripe_admin_js');
}

function enqueue_stripe_admin_js()
{
    wp_enqueue_script('stripe-s2', plugins_url('tooltip.js', __FILE__));
}

function cwd_stripe_checkout()
{
    if ($_POST['setstripe']) {
        $pkey = $_POST['pkey'];
        $skey = $_POST['skey'];
        update_option('stripepkey', $pkey);
        update_option('stripeskey', $skey);
    }

    if (get_option('stripeskey')) {
        try {
            require_once('stripe/lib/Stripe.php');
            Stripe2 :: setApiKey(get_option('stripeskey'));
            $account  = Stripe_Account2::retrieve();
            $plans    = Stripe_Plan2 :: all();
            $plandata = json_decode($plans, true);
        } catch (Exception $e) {
            delete_option('stripepkey');
            delete_option('stripeskey');
        }
    }

    if (get_option('stripe_s2member_key')) {
        if (strlen(get_option('stripe_s2member_key') < 1)) {
            update_option('stripe_s2member_key', c_ws_plugin__s2member_pro_remote_ops::remote_ops_key_gen());
        }
    }

    if ($_POST['update_stripe_role'] == 'Save') {
        $roles = get_editable_roles();
        foreach ($roles as $k => $role) {
            update_option('stripe_role_type_' . $k, $_POST['stripe_role_check_' . $k]);
            if (strpos(strtolower($k), 's2member') !== false) {
                if ($_POST['stripe_role_check_' . $k] == 1) {
                    update_option('stripe_role_' . $k, $_POST['stripe_role_' . $k]);
                } else {
                    update_option('stripe_role_amount_' . $k, $_POST['stripe_role_amount_' . $k]);
                    update_option('stripe_role_title_' . $k, $_POST['stripe_role_title_' . $k]);
                }
            }
        }
    }

    if ($_POST['thankyou_page']) {
        update_option('stripe_thankyou_page', $_POST['thankyou_page']);
    }

    ?>

    <style>
        .stripe-step {
            background: none repeat scroll 0 0 #FFFFFF;
            border: 1px solid #ccc;
            margin: 10px 20px 10px 0px;
            padding: 10px;
        }

        .success {
            margin-bottom: 10px;
        }

        .currency {
            display: none;
        }

        .stripe1, .stripe2 {
            background: #E1E1E1;
            padding: 10px;
        }

        .stripe-row {
            border-bottom: 1px solid #E1E1E1;
        }

    </style>


    <div class='stripe-step'>

        <h3>Step 1: Enter Stripe Api credentials <span class="has-tip"
                                                       title="<ul style='list-style:disc;'><li style='margin-left:10px;'>Visit http://stripe.com.</li><li style='margin-left:10px;'>Click on 'Your account' link.</li><li style='margin-left:10px;'>Navigate to Api Keys</li><li style='margin-left:10px;'>Use the test or Live keys.</li><li style='margin-left:10px;'>To test with test keys use the card no 4242 4242 4242 4242 with a future date.</li></ul>"><img
                    src="<?php echo plugins_url('help.png', __FILE__); ?>" alt=""/></span></h3>

        <form action="" method="POST">
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <td scope="row">Publishable Key:</td>
                    <td><input type="text" name="pkey" value="<?php echo get_option('stripepkey'); ?>" size="35"></td>
                </tr>
                <tr valign="top">
                    <td scope="row">Secret Key:</td>
                    <td><input type="text" name="skey" value="<?php echo get_option('stripeskey'); ?>" size="35">
                    </td>
                </tr>
                <tr valign="top">
                    <th></th>
                    <td><input type="submit" name="setstripe" value="Save" class="button-primary" id="set_stripe"/></td>
                </tr>


                </tbody>
            </table>

        </form>
    </div>

    <?php if ($plandata) { ?>
    <div class='stripe-step'>
        <h3>Step 2: Connect to Stripe</h3>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <td colspan='2'>
                    <?php
                    try {
                        require_once('stripe/lib/Stripe.php');
                        Stripe2 :: setApiKey(get_option('stripeskey'));
                        $for_plan = Stripe_Plan2 :: all();
                        $check    = json_decode($for_plan, true);
                        if (!$check) {
                            throw new Exception();
                        } else {
                            ?>
                            <div class='success'><a class="button-primary"
                                                    href="?page=cwd_stripe&action=disconnect_from_stripe">Disconnect
                                    From Stripe</a></div>
                            <div class='success'><img src="<?php echo plugins_url('check.png', __FILE__) ?>" alt=""/>
                                You are connected to the Stripe API.
                            </div>

                        <?php
                        }
                    } catch (Exception $e) {
                        delete_option('stripepkey');
                        delete_option('stripeskey');
                        echo "<div class='error'><p>Please enter valid stripe secret key and publishable key</p></div>";
                    }

                    ?>

                </td>
            </tr>
            </tbody>
        </table>
    </div>



    <div class='stripe-step'>
        <h3>Step 3: Link Plans/Product to Roles</h3>

        <form action="" method='post'>
            <table class="form-table widefat" style='width:850px;'>
                <tr class='stripe-row'>
                    <td valign='top'><b>Roles</b></td>
                    <td><b>Plans</b></td>
                    <td><b>Products</b></td>
                </tr>
                <?php
                $account_data = json_decode($account, true);
                if ($account_data['default_currency'] == 'gbp') {
                    $currency = '&pound;';
                }
                if ($account_data['default_currency'] == 'usd') {
                    $currency = '$';
                }
                if ($account_data['default_currency'] == 'eur') {
                    $currency = '&euro;';
                }
                $roles = get_editable_roles();
                foreach ($roles as $k => $role) {
                    if (strpos(strtolower($k), 's2member') !== false) {
                        $type = get_option('stripe_role_type_' . $k);
                        echo "<tr class='stripe-row'><td>" . $role['name'] . "</td><td>";
                        if ($type == 1) {
                            echo "<div class='stripe" . $type . "'><input type='radio' name='stripe_role_check_" . $k
                                . "' value='1' checked='checked' />";
                        } else {
                            echo "<div><input type='radio' name='stripe_role_check_" . $k . "' value='1' />";
                        }

                        echo "<select name='stripe_role_" . $k . "'><option value=''>Select Plan</option>";
                        foreach ($plandata['data'] as $value) {
                            if (get_option('stripe_role_' . $k) == $value['id']) {
                                echo '<option value="' . $value['id'] . '" selected="selected">' . $value['name']
                                    . '</option>';
                            } else {
                                echo '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
                            }
                            $i++;
                        }
                        echo "</select></div></td><td>";

                        if ($type == 2) {
                            echo "<div class='stripe" . $type
                                . "'><input type='radio' checked='checked' name='stripe_role_check_" . $k
                                . "' value='2'/>";
                        } else {
                            echo "<div><input type='radio' name='stripe_role_check_" . $k . "' value='2'/>";
                        }
                        if (strlen(get_option('stripe_role_amount_' . $k)) > 0) {
                            echo "<input type='text' name='stripe_role_title_" . $k
                                . "' placeholder='Product Name' value='" . get_option('stripe_role_title_' . $k)
                                . "'/>&nbsp;&nbsp;<span class='currency' style='display:inline'>" . $currency
                                . "</span><input type='text' class='stripe_product_amount' name='stripe_role_amount_"
                                . $k . "' size='4' placeholder='" . $currency . "' value='" . get_option(
                                    'stripe_role_amount_' . $k
                                ) . "'/> ";
                        } else {
                            echo "<input type='text' name='stripe_role_title_" . $k
                                . "' placeholder='Product Name' value='" . get_option('stripe_role_title_' . $k)
                                . "'/>&nbsp;&nbsp;<span class='currency' >" . $currency
                                . "</span><input type='text' class='stripe_product_amount' name='stripe_role_amount_"
                                . $k . "' size='4' placeholder='" . $currency . "' value='" . get_option(
                                    'stripe_role_amount_' . $k
                                ) . "'/> ";
                        }

                        echo "</div></td></tr>";
                    }
                }
                //
                ?>
                <tr valign='top' class='stripe-row'>
                    <th></th>
                    <td><input type='submit' class="button button-primary" name='update_stripe_role' value='Save'/></td>
                </tr>
            </table>
        </form>
    </div>

    <div class='stripe-step'>
        <h3>Step 4: Confirmation Page</h3>

        <form action="" method='post'>
            <table class="form-table widefat" style='width:850px;'>
                <tr class='stripe-row'>
                    <td valign='top' colspan='2'>
                        <label>Select Page</label>
                        <select name="thankyou_page">
                            <option value="">Select Page</option>
                            <?php
                            $pages = get_posts('post_type=page&numberposts=-1');
                            foreach ($pages as $page) {
                                if ($page->ID == get_option('stripe_thankyou_page')) {
                                    echo "<option value='" . $page->ID . "' selected='selected'>" . $page->post_title
                                        . "</option>";
                                } else {
                                    echo "<option value='" . $page->ID . "'>" . $page->post_title . "</option>";
                                }
                            }
                            //
                            ?>
                        </select>
                </tr>
                <tr valign='top' class='stripe-row'>
                    <th></th>
                    <td><input type='submit' class="button button-primary" name='thankyou_page_submit' value='Save'/>
                    </td>
                </tr>
            </table>
        </form>
    </div>

<?php
}
}


add_action('admin_init', 'cwd_disconnect_from_stripe');

function cwd_disconnect_from_stripe()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'disconnect_from_stripe') {
        delete_option('stripepkey');
        delete_option('stripeskey');
        wp_redirect(get_bloginfo('url') . '/wp-admin/admin.php?page=cwd_stripe');
    }
}


add_shortcode('stripe_s2', 'stripe_s2');
function stripe_s2($attr)
{
    if (get_option('stripeskey') !== false && get_option('stripepkey') !== false) {

        require_once('stripe/lib/Stripe.php');
        Stripe2 :: setApiKey(get_option('stripeskey'));
        $account      = Stripe_Account2::retrieve();
        $account_data = json_decode($account, true);

        if ($attr['role'] && get_option('stripe_role_type_' . $attr['role']) == 1) {
            $plan = get_option('stripe_role_' . $attr['role']);
            try {
                $plans     = Stripe_Plan2 :: retrieve(strtolower($plan));
                $plan_data = json_decode($plans, true);


                ?>

                <button class="stripes2-<?php echo $plan; ?> <?php echo $attr['class']; ?>"
                        data-plan='<?php echo strtolower($plan); ?>' level='<?php echo $attr['role'] ?>'>Pay Now
                </button>

                <script>
                    jQuery(document).ready(function () {
                        jQuery('.stripes2-<?php echo $plan; ?>').click(function (e) {
                            // Open Checkout with further options
                            jQuery('#stripe-plan').val(jQuery(this).attr('data-plan'));
                            jQuery('#stripe-product').val('');
                            jQuery('#stripe-level').val(jQuery(this).attr('level'));
                            handler.open({
                                name: '<?php echo $plan_data['name'] ?>',
                                description: '<?php echo $plan_data['statement_description'] ?>',
                                amount: <?php echo $plan_data['amount'];?>,
                                currency: '<?php echo $plan_data['currency']; ?>'
                            });

                            e.preventDefault();
                        });
                    });
                </script>



            <?php
            } catch (Exception $e) {
                ?>
                <div class='error'>Invalid Plan</div>
            <?php
            }
        }

        if ($attr['role'] && get_option('stripe_role_type_' . $attr['role']) == 2) {
            $product = get_option('stripe_role_title_' . $attr['role']);

            require_once('stripe/lib/Stripe.php');
            ?>

            <button class="stripes2-<?php echo preg_replace(
                '/[^A-Za-z0-9-]+/',
                '-',
                $product
            ); ?> <?php echo $attr['class']; ?>" level='<?php echo $attr['role'] ?>'>Pay Now
            </button>

            <script>
                jQuery(document).ready(function () {
                    jQuery('.stripes2-<?php echo preg_replace("/[^A-Za-z0-9-]+/", "-", $product); ?>').click(function (e) {
                        // Open Checkout with further options
                        jQuery('#stripe-product').val('<?php echo $product; ?>');
                        jQuery('#stripe-level').val(jQuery(this).attr('level'));
                        jQuery('#stripe-plan').val('');
                        handler.open({
                            name: '<?php echo $attr['product'] ?>',
                            amount: <?php echo 100 * get_option('stripe_role_amount_'.$attr['role']);?>,
                            currency: '<?php echo $account_data['default_currency']; ?>'
                        });

                        e.preventDefault();
                    });
                });
            </script>



        <?php
        }


    }
}

add_action('wp_footer', 'stripe_script');
function stripe_script()
{
    ?>
    <script type="text/javascript" src="<?php echo plugins_url('js-stripe.min.js', __FILE__);?>"></script>
    <script>
        Stripe.setPublishableKey('<?php echo get_option('stripepkey');?>');
        var handler = StripeCheckout.configure({
            key: '<?php echo get_option('stripepkey');?>',
            image: '<?php echo plugins_url('stripe_128.png', __FILE__);?>',
            token: function (response) {
                // Use the token to create the charge with a server-side script.
                // You can access the token ID with `token.id`


                Stripe.getToken(response.id, function (status, r) {
                    if (status == 200 && !r.used) {
                        jQuery.ajax({
                            url: '<?php echo get_bloginfo('url') ?>/wp-admin/admin-ajax.php?action=create_s2user_with_stripe',
                            type: 'POST',
                            data: {product: jQuery('#stripe-product').val(), plan: jQuery('#stripe-plan').val(), level: jQuery('#stripe-level').val(), stripeToken: response.id, email: response.email},
                            success: function (data) {
                                window.location = '<?php echo get_permalink(get_option('stripe_thankyou_page')); ?>'
                            }
                        });
                    } else {
                        alert('There is an issue with stripe, Please try again.');
                    }
                });


            }
        });
    </script>
    <input type="hidden" id='stripe-plan'/>
    <input type="hidden" id='stripe-level'/>
    <input type="hidden" id='stripe-product'/>
<?php
}

//User creation
add_action('wp_ajax_nopriv_create_s2user_with_stripe', 'create_s2user_with_stripe');
add_action('wp_ajax_create_s2user_with_stripe', 'create_s2user_with_stripe');
function create_s2user_with_stripe()
{
    if ($_POST['stripeToken']) {
        require_once('stripe/lib/Stripe.php');
        Stripe2 :: setApiKey(get_option('stripeskey'));
        $token = $_POST['stripeToken'];
        if (strlen($_POST['plan']) > 1) {
            $customer = Stripe_Customer2::create(
                array("card" => $token, "plan" => $_POST['plan'], "email" => $_POST['email'])
            );
            $level    = str_replace('s2member_level', '', $_POST['level']);
        } else {
            $token        = $_POST['stripeToken'];
            $customer     = Stripe_Customer2::create(array("card" => $token, "email" => $_POST['email']));
            $d            = json_decode($customer, true);
            $account      = Stripe_Account2::retrieve();
            $account_data = json_decode($account, true);
            Stripe_Charge2::create(
                array(
                    "amount"   => 100 * get_option('stripe_role_amount_' . $_POST['level']),
                    "currency" => $account_data['default_currency'],
                    'customer' => $d['id']
                )
            );
            $level = str_replace('s2member_level', '', $_POST['level']);
        }
        $d = json_decode($customer, true);


        if ($level < 1) {
            $level = 0;
        }
        $id = $d['id'];
        if (strlen($id) > 1) {
            $position      = strpos($_POST['email'], '@');
            $user_name     = substr($_POST['email'], 0, $position);
            $op["op"]      = "create_user"; // The Remote Operation.
            $op["api_key"] = get_option('stripe_s2member_key'); // Check your Dashboard for this value.
            // See: `s2Member -� API / Scripting -� Remote Operations API -� API Key`
            $op["data"] = array(
                "user_login"               => $user_name,
                // Required. A unique Username. Lowercase alphanumerics/underscores.
                "user_email"               => $_POST['email'],
                // Required. A valid/unique Email Address for the new User.
                // These additional details are 100% completely optional.
                "modify_if_login_exists"   => "0",
                // Optional. Update/modify if ``user_login`` value already exists in the database?
                // A non-zero value tells s2Member to update/modify an existing account with the details you provide, if this Username already exists.
                "user_pass"                => wp_generate_password(),
                // Optional. Plain text Password. If empty, this will be auto-generated.
                "first_name"               => "",
                // Optional. First Name for the new User.
                "last_name"                => "",
                // Optional. Last Name for the new User.
                "s2member_level"           => $level,
                // Optional. Defaults to Level #0 (a Free Subscriber).
                "s2member_ccaps"           => "",
                // Optional. Comma-delimited list of Custom Capabilities.
                "s2member_registration_ip" => "",
                // Optional. User's IP Address. If empty, s2Member will fill this upon first login.
                "s2member_subscr_gateway"  => "paypal",
                // Optional. User's Paid Subscr. Gateway Code. One of: (paypal|alipay|authnet|ccbill|clickbank|google).
                "s2member_subscr_id"       => $id,
                // Optional. User's Paid Subscr. ID. For PayPal�, use their Subscription ID, or Recurring Profile ID.
                "s2member_custom"          => "",
                // Optional. If provided, should always start with your installation domain name (i.e. $_SERVER["HTTP_HOST"]).
                "s2member_auto_eot_time"   => $d['created'],
                // Optional. Can be any value that PHP's ``strtotime()`` function will understand (i.e. YYYY-MM-DD).
                "custom_fields"            => array('stripe' => 'yes'),
                // Optional. An array of Custom Registration/Profile Field ID's, with associative values.
                "s2member_notes"           => "Created this User via stripe call.",
                // Optional. Administrative notations.
                "opt_in"                   => "1",
                // Optional. A non-zero value tells s2Member to attempt to process any List Servers you've configured in the Dashboard area.
                // This may result in your mailing list provider sending the User/Member a subscription confirmation email (i.e. ... please confirm your subscription).
                "notification"             => "1",
                // Optional. A non-zero value tells s2Member to email the new User/Member their Username/Password.
                // The "notification" parameter also tells s2Member to notify the site Administrator about this new account.
            );

            if (email_exists($_REQUEST['email'])) {
                $headers = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
                wp_mail(
                    $_REQUEST['email'],
                    's2Member Registration',
                    'Your email already exists in our database. Please contact the site admin.',
                    $headers
                );
            } else {
                $post_data = stream_context_create(
                    array(
                        "http" => array(
                            "method"  => "POST",
                            "header"  => "Content-type: application/x-www-form-urlencoded",
                            "content" => "s2member_pro_remote_op=" . urlencode(serialize($op))
                        )
                    )
                );
                $result    = trim(
                    file_get_contents(get_bloginfo('url') . "/?s2member_pro_remote_op=1", false, $post_data)
                );
            }

        }
    }
    exit;
}


register_uninstall_hook(__FILE__, 'stripe_s2_data_delete');
function stripe_s2_data_delete()
{
    delete_option('stripeskey');
    delete_option('stripepkey');
}

?>